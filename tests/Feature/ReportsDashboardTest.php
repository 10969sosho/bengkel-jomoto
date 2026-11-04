<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_paid_revenue_breakdown_and_technician_duration(): void
    {
        $admin = User::create([
            'name' => 'Admin Demo', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'admin',
        ]);
        $customer = User::create([
            'name' => 'Customer Demo', 'email' => 'customer@example.test', 'password' => 'password', 'role' => 'customer',
        ]);
        $technician = User::create([
            'name' => 'Teknisi Demo', 'email' => 'technician@example.test', 'password' => 'password', 'role' => 'teknisi',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'L 1234 AB',
            'year' => 2024, 'fuel_type' => 'gasoline', 'last_odometer' => 1000,
        ]);
        $order = ServiceOrder::create([
            'wo_number' => 'WO-2611-0001', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id, 'customer_complaint' => 'Servis', 'service_type' => 'regular',
            'location_type' => 'offsite', 'visit_fee' => 50, 'status' => 'completed', 'actual_completion' => now(),
        ]);
        foreach ([['service', 100], ['sparepart', 200], ['sub_material', 50]] as [$type, $price]) {
            $order->items()->create([
                'item_type' => $type, 'item_name' => $type, 'quantity' => 1, 'unit_price' => $price,
                'subtotal' => $price, 'is_free' => false,
            ]);
        }
        $invoice = Invoice::createFromOrder($order);
        $invoice->markPaid('qris');
        WorkLog::create([
            'service_order_id' => $order->id, 'technician_id' => $technician->id,
            'started_at' => now()->subHour(), 'stopped_at' => now(), 'duration_seconds' => 3600,
        ]);

        $this->withSession(['demo_user_id' => $admin->id])->get('/admin/reports?period=today')
            ->assertOk()
            ->assertSee('Rp 444')
            ->assertSee('Jasa Servis')
            ->assertSee('Sparepart (HET)')
            ->assertSee('Sub-Material Custom')
            ->assertSee('QRIS')
            ->assertSee('Teknisi Demo')
            ->assertSee('60 menit');
    }
}
