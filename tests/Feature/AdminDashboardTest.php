<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_and_view_orders_without_error(): void
    {
        $admin = User::create([
            'name' => 'Admin Bengkel',
            'email' => 'admin@jomoto.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'plate_number' => 'L 1234 ABC',
            'year' => 2023,
            'transmission' => 'Otomatis / CVT',
            'fuel_type' => 'gasoline',
            'last_odometer' => 1000,
        ]);

        // Order without technician (to test null technician fallback)
        ServiceOrder::create([
            'wo_number' => 'WO-TEST-0001',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Mesin bunyi',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'odometer' => 1000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.index'));

        $response->assertOk()
            ->assertSee('Dashboard Utama Bengkel')
            ->assertSee('WO-TEST-0001')
            ->assertSee('—');
    }
}
