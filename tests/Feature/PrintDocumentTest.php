<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_print_page_provides_custom_paper_size_controls(): void
    {
        $invoice = $this->invoice();

        $this->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee('Ukuran Kertas')
            ->assertSee('Custom (tentukan sendiri)')
            ->assertSee('Struk / Thermal · 80 mm')
            ->assertSee('@page', false);
    }

    public function test_service_adviser_can_print_the_estimation_sheet_with_both_approval_blocks(): void
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

        $this->withSession(['demo_user_id' => $sa->id])
            ->get(route('sa.print.estimate', $order))
            ->assertOk()
            ->assertSee('Lembar Estimasi Servis')
            ->assertSee('Persetujuan Estimasi')
            ->assertSee('Persetujuan Hasil Pengerjaan')
            ->assertSee('TOTAL ESTIMASI')
            ->assertSee('Rp 94,350')
            ->assertSee('@page', false);

        $this->withSession(['demo_user_id' => $technician->id])
            ->get(route('sa.print.estimate', $order))
            ->assertRedirectToRoute('dashboard');
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

    private function order(User $customer, Vehicle $vehicle, User $sa, User $technician): ServiceOrder
    {
        return ServiceOrder::create([
            'wo_number' => 'WO-2611-0041', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'sa_id' => $sa->id, 'technician_id' => $technician->id, 'customer_complaint' => 'Rem depan bunyi',
            'service_type' => 'regular', 'location_type' => 'onsite', 'status' => 'waiting_approval',
        ]);
    }

    private function invoice(): Invoice
    {
        $customer = $this->user('Billing Customer', 'customer');
        $vehicle = $this->vehicle($customer);
        $technician = $this->user('Billing Technician', 'teknisi');
        $order = ServiceOrder::create([
            'wo_number' => 'WO-2611-0042', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id, 'customer_complaint' => 'Servis', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'invoiced',
        ]);
        $order->items()->create([
            'item_type' => 'service', 'item_name' => 'Servis Ringan', 'quantity' => 1,
            'unit_price' => 1000, 'subtotal' => 1000, 'is_free' => false,
        ]);

        return Invoice::createFromOrder($order);
    }
}
