<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_without_a_bearer_token(): void
    {
        $this->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_creates_home_service_work_order_and_detects_jomoto_coupon(): void
    {
        $customer = User::create([
            'name' => 'Demo Customer', 'email' => 'customer@example.test', 'password' => 'password',
            'role' => 'customer', 'whatsapp' => '6281234567890', 'ecommerce_user_id' => 77,
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'brand' => 'Honda', 'model' => 'Vario',
            'plate_number' => 'L 1234 AB', 'year' => 2024, 'fuel_type' => 'gasoline', 'last_odometer' => 9900,
        ]);

        $response = $this->withToken('demo-erp-jomoto-token')->postJson('/api/v1/service-orders', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Servis berkala dan pemeriksaan CVT',
            'odometer' => 10050,
            'location_type' => 'offsite',
            'offsite_address' => 'Jl. Raya Darmo No. 10',
            'location_notes' => 'Pagar hitam',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_type', 'free_jomoto')
            ->assertJsonPath('data.coupon_milestone', 10000)
            ->assertJsonPath('data.visit_fee', 50000);

        $this->assertDatabaseHas('service_orders', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'location_type' => 'offsite',
            'coupon_milestone' => 10000,
            'offsite_address' => 'Jl. Raya Darmo No. 10',
        ]);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'last_odometer' => 10050]);

        $this->withToken('demo-erp-jomoto-token')->postJson('/api/v1/service-orders', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Servis berkala',
            'odometer' => 10080,
            'location_type' => 'onsite',
        ])->assertCreated()->assertJsonPath('data.service_type', 'regular')
            ->assertJsonPath('data.coupon_milestone', null);

        $this->assertDatabaseCount('service_orders', 2);
    }

    public function test_rejects_home_service_without_an_address(): void
    {
        $customer = User::create([
            'name' => 'Demo Customer', 'email' => 'customer@example.test', 'password' => 'password', 'role' => 'customer',
        ]);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id, 'brand' => 'Honda', 'model' => 'Vario', 'plate_number' => 'L 1234 AB',
            'year' => 2024, 'fuel_type' => 'gasoline', 'last_odometer' => 100,
        ]);

        $this->withToken('demo-erp-jomoto-token')->postJson('/api/v1/service-orders', [
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Periksa motor', 'odometer' => 100,
            'location_type' => 'offsite',
        ])->assertUnprocessable()->assertJsonValidationErrors('offsite_address');

        $this->assertDatabaseCount('service_orders', 0);
    }
}
