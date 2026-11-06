<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFlowApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_is_issued_only_after_estimate_approval_and_result_confirmation(): void
    {
        $sa = $this->user('Rina Wulandari', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $customer = $this->user('Hendra Wijaya', 'customer');
        $vehicle = $this->vehicle($customer);
        $order = $this->order($customer, $vehicle, $sa, $technician);
        $order->items()->create([
            'item_type' => 'service', 'item_name' => 'Servis Berkala Ringan', 'quantity' => 1,
            'unit_price' => 85000, 'subtotal' => 85000, 'is_free' => false,
        ]);

        // Tahap 1 — customer menyetujui estimasi dari portal.
        $this->withSession(['demo_user_id' => $customer->id])
            ->post(route('customer.orders.approve', $order))
            ->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertNotNull($order->estimate_approved_at);

        // Tahap 7 — teknisi mengerjakan dan menyelesaikan pekerjaan.
        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'start']);
        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.complete', $order), ['notes' => 'Ganti kampas rem depan'])
            ->assertSessionHas('success');
        $this->assertSame('completed', $order->fresh()->status);

        // Tahap 10 — invoice belum boleh terbit sebelum pemeriksaan akhir & konfirmasi customer.
        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.invoice', $order))
            ->assertSessionHas('error', 'Invoice hanya bisa diterbitkan setelah customer mengonfirmasi hasil pengerjaan.');
        $this->assertNull($order->fresh()->invoice);

        // Tahap 8 — pemeriksaan akhir SA wajib berisi catatan hasil.
        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.finalCheck', $order))
            ->assertSessionHasErrors('final_check_notes');

        $checkNotes = 'Kampas rem terpasang, tes jalan normal, tidak ada kebocoran.';
        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.finalCheck', $order), ['final_check_notes' => $checkNotes])
            ->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('waiting_confirmation', $order->status);
        $this->assertNotNull($order->final_check_at);

        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.invoice', $order))
            ->assertSessionHas('error');

        // Tahap 9 — customer melihat hasil pemeriksaan lalu mengonfirmasi hasil pengerjaan.
        $this->withSession(['demo_user_id' => $customer->id])
            ->get(route('customer.index'))
            ->assertOk()
            ->assertSee('Persetujuan Hasil Pengerjaan')
            ->assertSee($checkNotes);

        $this->withSession(['demo_user_id' => $customer->id])
            ->post(route('customer.orders.confirm', $order))
            ->assertSessionHasErrors('confirmation_note');

        $this->withSession(['demo_user_id' => $customer->id])
            ->post(route('customer.orders.confirm', $order), [
                'confirmation_note' => 'Hasil pengerjaan sudah sesuai dan disetujui.',
            ])
            ->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertNotNull($order->result_confirmed_at);

        // Tahap 10 — invoice akhirnya diterbitkan SA.
        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.invoice', $order))
            ->assertRedirectToRoute('invoices.print', $order->fresh()->invoice);
        $this->assertSame('invoiced', $order->fresh()->status);
    }

    public function test_service_adviser_can_record_both_customer_approvals_on_behalf_of_the_customer(): void
    {
        $sa = $this->user('Rina Wulandari', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $customer = $this->user('Siti Rahma', 'customer');
        $vehicle = $this->vehicle($customer);
        $order = $this->order($customer, $vehicle, $sa, $technician, 'WO-2611-0021');

        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.approve', $order))
            ->assertSessionHas('success');
        $this->assertSame('approved', $order->fresh()->status);

        $order->update(['status' => 'waiting_confirmation', 'final_check_at' => now(), 'final_check_notes' => 'Tes jalan aman.']);

        $this->withSession(['demo_user_id' => $sa->id])
            ->post(route('sa.confirm', $order), ['result_confirmation_notes' => 'Customer setuju via WA.'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('Customer setuju via WA.', $order->result_confirmation_notes);
    }

    public function test_service_flow_tracker_reports_the_current_stage(): void
    {
        $sa = $this->user('Rina Wulandari', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $customer = $this->user('Citra Permata', 'customer');
        $vehicle = $this->vehicle($customer);
        $order = $this->order($customer, $vehicle, $sa, $technician, 'WO-2611-0031');

        $this->assertSame(3, $order->currentFlowStage(), 'WO sudah ditangani SA namun belum diperiksa mekanik.');

        $order->inspection()->create([
            'technician_id' => $technician->id,
            'checklist' => [['key' => 'rem_depan', 'label' => 'Rem Depan', 'status' => 'ok']],
            'findings' => 'Kampas rem tipis.',
        ]);
        $order->items()->create([
            'item_type' => 'service', 'item_name' => 'Ganti Kampas Rem', 'quantity' => 1,
            'unit_price' => 150000, 'subtotal' => 150000, 'is_free' => false,
        ]);
        $order->update(['status' => 'waiting_confirmation', 'final_check_at' => now(), 'result_confirmed_at' => now()]);

        $stages = $order->fresh()->flowStages();
        $this->assertCount(12, $stages);
        $this->assertTrue($stages[7]['done'], 'Tahap Pemeriksaan Akhir SA selesai.');
        $this->assertFalse($stages[9]['done'], 'Tahap Invoice belum selesai.');
        $this->assertSame(10, $order->fresh()->currentFlowStage());

        $this->withSession(['demo_user_id' => $sa->id])
            ->get(route('sa.show', $order))
            ->assertOk()
            ->assertSee('Alur Servis')
            ->assertSee('Tahap 10 dari 12');
    }

    public function test_customer_cannot_approve_another_customers_estimation(): void
    {
        $sa = $this->user('Rina Wulandari', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $owner = $this->user('Hendra Wijaya', 'customer');
        $intruder = $this->user('Penyusup', 'customer');
        $vehicle = $this->vehicle($owner);
        $order = $this->order($owner, $vehicle, $sa, $technician);

        $this->withSession(['demo_user_id' => $intruder->id])
            ->post(route('customer.orders.approve', $order))
            ->assertForbidden();
        $this->withSession(['demo_user_id' => $intruder->id])
            ->post(route('customer.orders.confirm', $order))
            ->assertForbidden();
        $this->assertSame('waiting_approval', $order->fresh()->status);
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

    private function order(User $customer, Vehicle $vehicle, User $sa, User $technician, string $number = 'WO-2611-0001'): ServiceOrder
    {
        return ServiceOrder::create([
            'wo_number' => $number, 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'sa_id' => $sa->id, 'technician_id' => $technician->id,
            'customer_complaint' => 'Rem depan bunyi', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'waiting_approval',
        ]);
    }
}
