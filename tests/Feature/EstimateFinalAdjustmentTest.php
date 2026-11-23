<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateFinalAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sa_can_adjust_estimate_during_final_check_before_invoice_is_issued(): void
    {
        $sa = $this->user('Rina Wulandari', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $customer = $this->user('Hendra Wijaya', 'customer');
        $vehicle = $this->vehicle($customer);
        $order = $this->completedOrder($customer, $vehicle, $sa, $technician);

        // Form penyesuaian estimasi tersedia di tahap pemeriksaan akhir.
        $this->actingAs($sa)
            ->get(route('sa.show', $order))
            ->assertOk()
            ->assertSee('Sesuaikan / Edit Estimasi Biaya');

        // SA menyesuaikan item estimasi (ubah harga, tambah item, diskon global).
        $this->actingAs($sa)
            ->post(route('sa.estimate', $order), [
                'sa_diagnosis' => 'Ganti kampas rem depan + tune up ringan.',
                'estimate_discount' => 20000,
                'items' => [
                    [
                        'item_type' => 'service', 'category' => 'Umum', 'item_name' => 'Ganti Kampas Rem',
                        'service_action' => 'G', 'quantity' => 1, 'unit_price' => 125000, 'discount' => 0,
                    ],
                    [
                        'item_type' => 'sparepart', 'item_name' => 'Kampas Rem Depan',
                        'quantity' => 2, 'unit_price' => 50000, 'discount' => 0,
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('completed', $order->status, 'Penyesuaian estimasi tidak mengubah status pemeriksaan akhir.');
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(225000, (int) $order->items()->sum('subtotal'));
        $this->assertSame(20000, (int) $order->estimate_discount);
        $this->assertSame(22550, (int) $order->estimate_tax, 'PPN 11% dihitung ulang dari total setelah diskon.');

        // Alur dilanjutkan: pemeriksaan akhir → konfirmasi customer → invoice.
        $this->actingAs($sa)
            ->post(route('sa.finalCheck', $order), ['final_check_notes' => 'Kampas terpasang, tes jalan normal.'])
            ->assertSessionHas('success');
        $this->actingAs($sa)
            ->post(route('sa.confirm', $order), ['result_confirmation_notes' => 'Customer setuju.'])
            ->assertSessionHas('success');

        $this->actingAs($sa)
            ->post(route('sa.invoice', $order))
            ->assertRedirectToRoute('invoices.print', $order->fresh()->invoice);

        /** @var Invoice $invoice */
        $invoice = $order->fresh()->invoice;
        $this->assertSame('invoiced', $order->fresh()->status);
        $this->assertSame(225000, (int) ($invoice->total_services + $invoice->total_spareparts + $invoice->total_submaterials));
        $this->assertSame(20000, (int) $invoice->discount_amount, 'Invoice membawa diskon global estimasi terbaru.');
        $this->assertSame(22550, (int) $invoice->tax_amount);
        $this->assertSame(227550, (int) $invoice->grand_total, 'Invoice mengonversi estimasi yang sudah disesuaikan SA.');
    }

    private function user(string $name, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function vehicle(User $customer): Vehicle
    {
        return $customer->vehicles()->create([
            'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'L 1234 AB', 'year' => 2024,
            'fuel_type' => 'gasoline', 'last_odometer' => 1000,
        ]);
    }

    private function completedOrder(User $customer, Vehicle $vehicle, User $sa, User $technician): ServiceOrder
    {
        $order = ServiceOrder::create([
            'wo_number' => 'WO-2611-0101', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'sa_id' => $sa->id, 'technician_id' => $technician->id,
            'customer_complaint' => 'Rem depan bunyi', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'completed', 'odometer' => 1500,
            'estimate_number' => 'PW/11/2026/00001',
        ]);

        $order->inspection()->create([
            'technician_id' => $technician->id,
            'checklist' => [['key' => 'pengereman', 'label' => 'Sistem Pengereman', 'status' => 'g']],
            'findings' => 'Kampas rem depan tipis.',
        ]);

        $order->items()->create([
            'item_type' => 'service', 'item_name' => 'Servis Berkala Ringan', 'quantity' => 1,
            'unit_price' => 85000, 'subtotal' => 85000, 'is_free' => false,
        ]);

        return $order;
    }
}
