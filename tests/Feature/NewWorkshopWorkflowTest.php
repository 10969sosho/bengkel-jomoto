<?php

namespace Tests\Feature;

use App\Models\Inspection;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewWorkshopWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_separate_pricelist_and_sub_material_pages(): void
    {
        $admin = User::create([
            'name' => 'Admin Bengkel',
            'email' => 'admin@jomoto.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        ServicePricelist::create([
            'service_name' => 'Paket Servis CVT Complete',
            'category' => 'cvt',
            'price' => 75000,
            'is_active' => true,
        ]);

        SubMaterialCatalog::create([
            'name' => 'Brake Cleaner Wurth',
            'code' => 'SUB-BC',
            'unit' => 'kaleng',
            'default_price' => 35000,
            'is_active' => true,
        ]);

        // Dashboard admin no longer dumps full tables
        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee(route('admin.pricelist'))
            ->assertSee(route('admin.subMaterials'));

        // Pricelist page
        $this->actingAs($admin)->get(route('admin.pricelist'))
            ->assertOk()
            ->assertSee('Pricelist Jasa Standar')
            ->assertSee('Paket Servis CVT Complete')
            ->assertSee('Rp 75,000');

        // Sub Material page
        $this->actingAs($admin)->get(route('admin.subMaterials'))
            ->assertOk()
            ->assertSee('Katalog Sub-Material')
            ->assertSee('Brake Cleaner Wurth')
            ->assertSee('Rp 35,000');
    }

    public function test_customer_booking_form_hides_p_s_g_badges_and_shows_clean_services(): void
    {
        $customer = User::create([
            'name' => 'Budi Pelanggan',
            'email' => 'budi@test.com',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $customer->vehicles()->create([
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'plate_number' => 'B 1234 XYZ',
            'year' => 2023,
            'fuel_type' => 'gasoline',
            'last_odometer' => 5000,
        ]);

        ServicePricelist::create([
            'service_name' => 'Tune Up Injeksi',
            'category' => 'mesin',
            'price' => 85000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.booking.create'));

        $response->assertOk()
            ->assertSee('Tune Up Injeksi')
            ->assertDontSee('P · Perlu Perhatian')
            ->assertDontSee('S · Perlu Servis')
            ->assertDontSee('G · Perlu Ganti');
    }

    public function test_sa_dashboard_provides_booking_detail_and_checkin_navigation(): void
    {
        $sa = User::create([
            'name' => 'Siti SA',
            'email' => 'siti@jomoto.test',
            'password' => 'password',
            'role' => 'sa',
        ]);

        $customer = User::create([
            'name' => 'Agus Pelanggan',
            'email' => 'agus@test.com',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $vehicle = $customer->vehicles()->create([
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'plate_number' => 'L 9876 AB',
            'year' => 2022,
            'fuel_type' => 'gasoline',
            'last_odometer' => 12000,
        ]);

        $booking = ServiceOrder::create([
            'wo_number' => 'WO-2611-9901',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Tarikan gas berat dan brebet',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'odometer' => 12000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($sa)->get(route('sa.index'));
        $response->assertOk()
            ->assertSee('Lihat Detail Booking')
            ->assertSee('Lanjutkan Pemeriksaan Awal')
            ->assertSee(route('sa.bookings.show', $booking))
            ->assertSee(route('sa.checkin.create', ['booking_id' => $booking->id]));

        // Check booking detail page
        $this->actingAs($sa)->get(route('sa.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Detail Booking Service')
            ->assertSee('Tarikan gas berat dan brebet')
            ->assertSee('L 9876 AB')
            ->assertSee('Lanjutkan Pemeriksaan Awal');
    }

    public function test_complete_five_stage_workflow_checkin_mechanic_estimate_customer_and_spk(): void
    {
        Storage::fake('public');

        $sa = User::create(['name' => 'Rina SA', 'email' => 'rina.sa@test.com', 'password' => 'password', 'role' => 'sa']);
        $mechanic = User::create(['name' => 'Joko Mekanik', 'email' => 'joko.mekanik@test.com', 'password' => 'password', 'role' => 'teknisi']);
        $customer = User::create(['name' => 'Rudy Client', 'email' => 'rudy.client@test.com', 'password' => 'password', 'role' => 'customer']);

        $vehicle = $customer->vehicles()->create([
            'brand' => 'Honda', 'model' => 'PCX 160', 'plate_number' => 'D 5555 JMT', 'year' => 2023,
            'fuel_type' => 'gasoline', 'last_odometer' => 8000,
        ]);

        $booking = ServiceOrder::create([
            'wo_number' => 'WO-2611-0099',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Getar di putaran bawah',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'odometer' => 8100,
            'status' => 'draft',
        ]);

        // Stage 1: SA performs Check-in
        $this->actingAs($sa)->get(route('sa.checkin.create', ['booking_id' => $booking->id]))
            ->assertOk()
            ->assertSee('Pemeriksaan Awal (Check-in)')
            ->assertSee('D 5555 JMT');

        $checkinResponse = $this->actingAs($sa)->post(route('sa.checkin.store'), [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'technician_id' => $mechanic->id,
            'location_type' => 'onsite',
            'odometer' => 8150,
            'fuel_level' => '1/2',
            'manager_name' => 'ARTI DEWI',
            'customer_complaint' => 'Getar di putaran bawah dan CVT kotor',
            'sa_diagnosis' => 'Kemungkinan kampas ganda kotor atau mangkok aus',
            'documents' => [
                'stnk' => 'ada',
                'starter_key' => 'ada',
                'service_book' => 'tidak',
                'basic_tools' => 'ada',
            ],
            'exterior' => [
                'cover_body' => 'G',
                'jok' => 'G',
                'transmisi' => 'R',
            ],
        ]);

        $checkinResponse->assertRedirectToRoute('sa.show', $booking);
        $booking->refresh();

        $this->assertSame('inspected', $booking->status);
        $this->assertSame(8150, $booking->odometer);
        $this->assertNotNull($booking->inspection);
        $this->assertStringStartsWith('CI/', $booking->inspection->inspection_number);
        $this->assertSame('1/2', $booking->inspection->fuel_level);

        // Printable check-in page
        $this->actingAs($sa)->get(route('sa.print.checkin', $booking))
            ->assertOk()
            ->assertSee('PEMERIKSAAN AWAL (CHECK-IN)')
            ->assertSee($booking->inspection->inspection_number)
            ->assertSee('ARTI DEWI')
            ->assertSee('D 5555 JMT');

        // Stage 2: Mechanic confirms inspection
        $this->assertNull($booking->inspection->mechanic_confirmed_at);

        $confirmResponse = $this->actingAs($mechanic)->post(route('teknisi.confirmInspection', $booking), [
            'findings' => 'Roller peyang 2 biji dan kampas kopling tipis',
            'recommendations' => 'Ganti roller 1 set dan bersihkan mangkok kopling',
            'mechanic_notes' => 'Siap diproses estimasi oleh SA',
        ]);

        $confirmResponse->assertSessionHasNoErrors();
        $booking->refresh();
        $this->assertNotNull($booking->inspection->mechanic_confirmed_at);
        $this->assertTrue($booking->isMechanicConfirmed());

        // Stage 3: SA creates Estimasi Biaya
        $estimateResponse = $this->actingAs($sa)->post(route('sa.estimate', $booking), [
            'sa_diagnosis' => 'Disetujui penggantian roller dan servis CVT',
            'items' => [
                [
                    'item_type' => 'service',
                    'item_name' => 'Servis CVT',
                    'quantity' => 1,
                    'unit_price' => 50000,
                ],
                [
                    'item_type' => 'sparepart',
                    'item_name' => 'Roller Set PCX',
                    'quantity' => 1,
                    'unit_price' => 95000,
                ],
            ],
        ]);

        $estimateResponse->assertSessionHasNoErrors();
        $booking->refresh();

        $this->assertSame('waiting_approval', $booking->status);
        $this->assertNotNull($booking->estimate_number);
        $this->assertStringStartsWith('PW/', $booking->estimate_number);
        $this->assertCount(2, $booking->items);

        // Printable estimate page
        $this->actingAs($sa)->get(route('sa.print.estimate', $booking))
            ->assertOk()
            ->assertSee('Lembar Estimasi Servis')
            ->assertSee($booking->estimate_number)
            ->assertSee('Servis CVT')
            ->assertSee('Roller Set PCX')
            ->assertSee('TOTAL ESTIMASI');

        // Stage 4: Customer confirms / approves estimate
        $customerApprovalResponse = $this->actingAs($customer)->post(route('customer.orders.approve', $booking));
        $customerApprovalResponse->assertSessionHasNoErrors();
        $booking->refresh();

        $this->assertSame('approved', $booking->status);
        $this->assertNotNull($booking->estimate_approved_at);

        // Stage 5: SA issues SPK
        $this->assertNull($booking->spk_number);
        $spkResponse = $this->actingAs($sa)->post(route('sa.spk.issue', $booking));
        $spkResponse->assertSessionHasNoErrors();
        $booking->refresh();

        $this->assertNotNull($booking->spk_number);
        $this->assertStringStartsWith('SOJ/', $booking->spk_number);
        $this->assertNotNull($booking->spk_issued_at);
        $this->assertTrue($booking->isSpkIssued());

        // Printable SPK page
        $this->actingAs($sa)->get(route('sa.print.spk', $booking))
            ->assertOk()
            ->assertSee('SURAT PERINTAH KERJA (SPK)')
            ->assertSee($booking->spk_number)
            ->assertSee('Servis CVT')
            ->assertSee('Joko Mekanik');

        // Mechanic starts timer (start work)
        $timerResponse = $this->actingAs($mechanic)->post(route('teknisi.timer', $booking), [
            'action' => 'start',
        ]);
        $timerResponse->assertSessionHasNoErrors();
        $booking->refresh();

        $this->assertSame('working', $booking->status);
        $this->assertNotNull($booking->work_start_time);
        $this->assertSame(1, $booking->workLogs()->count());
    }
}
