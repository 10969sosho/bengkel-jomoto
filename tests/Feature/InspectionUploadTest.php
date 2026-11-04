<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InspectionUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_uploads_before_and_after_inspection_photos(): void
    {
        Storage::fake('public');
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
            'technician_id' => $technician->id, 'customer_complaint' => 'Pemeriksaan motor', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'working',
        ]);

        $response = $this->withSession(['demo_user_id' => $technician->id])->post(route('teknisi.inspect.save', $order), [
            'before_photos' => [UploadedFile::fake()->image('before.jpg')],
            'after_photos' => [UploadedFile::fake()->image('after.jpg')],
        ]);

        $response->assertRedirectToRoute('teknisi.index');
        $inspection = $order->fresh()->inspection;
        $this->assertModelExists($inspection);
        Storage::disk('public')->assertExists($inspection->before_photos[0]);
        Storage::disk('public')->assertExists($inspection->after_photos[0]);
    }
}
