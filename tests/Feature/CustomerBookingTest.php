<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_requires_service_selection_and_stnk_photo(): void
    {
        $customer = $this->user('Hendra Wijaya', 'customer');
        $vehicle = $this->vehicle($customer);

        $this->withSession(['demo_user_id' => $customer->id])->post(route('customer.orders.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Motor sering mati mendadak',
            'odometer' => 1500,
            'location_type' => 'onsite',
        ])->assertSessionHasErrors(['requested_services', 'stnk_photo']);

        $this->assertSame(0, ServiceOrder::count());
    }

    public function test_booking_rejects_unknown_service_option(): void
    {
        Storage::fake('public');
        $customer = $this->user('Siti Rahma', 'customer');
        $vehicle = $this->vehicle($customer);
        $pricelist = $this->pricelist('Servis Berkala Ringan', 85000);

        $this->withSession(['demo_user_id' => $customer->id])->post(route('customer.orders.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Ganti oli',
            'odometer' => 1600,
            'location_type' => 'onsite',
            'requested_services' => [$pricelist->id, 9999],
            'stnk_photo' => UploadedFile::fake()->image('stnk.jpg'),
        ])->assertSessionHasErrors('requested_services.1');

        $this->assertSame(0, ServiceOrder::count());
    }

    public function test_booking_service_choice_and_stnk_photo_are_received_by_service_adviser(): void
    {
        Storage::fake('public');
        $customer = $this->user('Citra Permata', 'customer');
        $sa = $this->user('Rina Wulandari', 'sa');
        $vehicle = $this->vehicle($customer);
        $reguler = $this->pricelist('Servis Berkala Ringan', 85000);
        $cvt = $this->pricelist('Servis CVT / Roller', 120000);

        $this->withSession(['demo_user_id' => $customer->id])->post(route('customer.orders.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'CVT bunyi & ganti oli',
            'odometer' => 4100,
            'location_type' => 'onsite',
            'requested_services' => [$reguler->id, $cvt->id],
            'stnk_photo' => UploadedFile::fake()->image('stnk.jpg'),
        ])->assertRedirectToRoute('customer.index');

        $order = ServiceOrder::firstOrFail();
        $this->assertSame([$reguler->id, $cvt->id], $order->requested_services);
        $this->assertSame(['Servis Berkala Ringan', 'Servis CVT / Roller'], $order->requestedServiceLabels());
        Storage::disk('public')->assertExists($order->stnk_photo);

        $this->withSession(['demo_user_id' => $customer->id])
            ->get(route('customer.index'))
            ->assertOk()
            ->assertSee('Servis Berkala Ringan')
            ->assertSee('Lihat Foto STNK');

        $this->withSession(['demo_user_id' => $sa->id])
            ->get(route('sa.index'))
            ->assertOk()
            ->assertSee('Booking Service Masuk')
            ->assertSee('Servis CVT / Roller')
            ->assertSee('Foto STNK');

        $this->withSession(['demo_user_id' => $sa->id])
            ->get(route('sa.show', $order))
            ->assertOk()
            ->assertSee('Layanan Diminta Customer')
            ->assertSee('Servis Berkala Ringan')
            ->assertSee('Lihat Foto STNK');
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
            'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'L 4192 CP', 'year' => 2024,
            'fuel_type' => 'gasoline', 'last_odometer' => 1000,
        ]);
    }

    private function pricelist(string $name, int $price): ServicePricelist
    {
        return ServicePricelist::create([
            'service_name' => $name,
            'category' => 'reguler',
            'price' => $price,
            'is_active' => true,
        ]);
    }
}
