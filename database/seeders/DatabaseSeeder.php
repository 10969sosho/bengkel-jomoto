<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\Sparepart;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password';

        $admin = User::create([
            'name' => 'Administrator Jomoto',
            'email' => 'admin@jomoto.com',
            'password' => $password,
            'phone' => '0812-3456-7800',
            'whatsapp' => '6281234567800',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $sa = User::create([
            'name' => 'Rina Wulandari',
            'email' => 'sa@jomoto.com',
            'password' => $password,
            'phone' => '0812-3456-7801',
            'whatsapp' => '6281234567801',
            'role' => 'sa',
            'email_verified_at' => now(),
        ]);

        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.teknisi@jomoto.com',
            'password' => $password,
            'phone' => '0812-3456-7802',
            'whatsapp' => '6281234567802',
            'role' => 'teknisi',
            'email_verified_at' => now(),
        ]);

        $agus = User::create([
            'name' => 'Agus Pratama',
            'email' => 'agus.teknisi@jomoto.com',
            'password' => $password,
            'phone' => '0812-3456-7803',
            'whatsapp' => '6281234567803',
            'role' => 'teknisi',
            'email_verified_at' => now(),
        ]);

        $hendra = User::create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@gmail.com',
            'password' => $password,
            'phone' => '0857-1111-2222',
            'whatsapp' => '6285711112222',
            'role' => 'customer',
            'ecommerce_user_id' => 1001,
            'email_verified_at' => now(),
        ]);

        $siti = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@gmail.com',
            'password' => $password,
            'phone' => '0857-3333-4444',
            'whatsapp' => '6285733334444',
            'role' => 'customer',
            'ecommerce_user_id' => 1002,
            'email_verified_at' => now(),
        ]);

        $citra = User::create([
            'name' => 'Citra Permata',
            'email' => 'citra@gmail.com',
            'password' => $password,
            'phone' => '0813-5555-8822',
            'whatsapp' => '6281355558822',
            'role' => 'customer',
            'ecommerce_user_id' => 1003,
            'email_verified_at' => now(),
        ]);

        // ── Master: Pricelist Servis ──
        $pricelist = [
            ['Servis Berkala Ringan', 'reguler', 85000],
            ['Servis Berkala Lengkap', 'reguler', 150000],
            ['Tune Up', 'reguler', 175000],
            ['Ganti Oli Mesin + Filter', 'reguler', 65000],
            ['Servis CVT / Roller', 'reguler', 120000],
            ['Pengecekan & Perbaikan Kelistrikan', 'reguler', 100000],
            ['Klaim Service Berkala 1.000 KM (ATPM)', 'free_atpm', 85000],
            ['Klaim Service Berkala 4.000 KM (ATPM)', 'free_atpm', 150000],
            ['Klaim Service Berkala 7.000 KM (ATPM)', 'free_atpm', 175000],
            ['Service Berkala 10.000 KM (Jomoto)', 'free_jomoto', 150000],
            ['Service Berkala 20.000 KM (Jomoto)', 'free_jomoto', 175000],
            ['Biaya Kunjungan Home Service', 'home_service', 50000],
            ['Jasa Teknisi Home Service', 'home_service', 75000],
        ];

        foreach ($pricelist as [$name, $category, $price]) {
            ServicePricelist::create([
                'service_name' => $name,
                'category' => $category,
                'price' => $price,
                'is_active' => true,
            ]);
        }

        // ── Master: 68 Item Pricelist Layanan (8 kategori, harga per aksi P/S/G, '-' = 0) ──
        foreach ($this->serviceCatalog() as [$category, $name, $priceP, $priceS, $priceG]) {
            ServicePricelist::create([
                'service_name' => $name,
                'category' => $category,
                'price' => $priceP ?: ($priceS ?: $priceG),
                'price_p' => $priceP,
                'price_s' => $priceS,
                'price_g' => $priceG,
                'is_active' => true,
            ]);
        }

        // ── Master: Katalog Sub-Material ──
        $subMaterials = [
            ['Gemuk / Pelumasan Khusus', 'Penggantian gemuk CVT, rantai, atau komponen lain', 25000],
            ['Gulung Dinamo', 'Jasa gulung ulang dinamo starter / alternator', 150000],
            ['Gerinda / Bubut / Las', 'Perbaikan mekanis dengan mesin gerinda, bubut, atau las', 75000],
            ['Repaint / Touch-up Cat Body', 'Perbaikan cat body atau touch-up goresan', 200000],
            ['Pembersihan Karburator', 'Cuci & kalibrasi karburator / throttle body', 45000],
            ['Kabel & Soket Kustom', 'Perbaikan atau penggantian kabel & soket kelistrikan', 35000],
        ];

        foreach ($subMaterials as [$name, $description, $price]) {
            SubMaterialCatalog::create([
                'name' => $name,
                'description' => $description,
                'default_price' => $price,
                'is_active' => true,
            ]);
        }

        foreach ([
            ['OLI-MPX2-1L', 'Oli Mesin MPX 2 1L', 'Honda', 'Beat / Vario', 78000, 24, 5001],
            ['PAD-HON-125', 'Kampas Rem Depan Honda', 'Honda', 'Beat / Vario', 85000, 11, 5002],
            ['BELT-VAR-160', 'V-Belt Vario 160', 'Honda', 'Vario 160', 145000, 7, 5003],
            ['ROLLER-GS-G1', 'Roller Set Gesits G1', 'Gesits', 'G1', 185000, 5, 5004],
        ] as [$sku, $name, $brand, $model, $price, $stock, $ecommerceProductId]) {
            Sparepart::create([
                'sku' => $sku,
                'name' => $name,
                'brand' => $brand,
                'model' => $model,
                'price' => $price,
                'stock' => $stock,
                'ecommerce_product_id' => $ecommerceProductId,
                'is_active' => true,
            ]);
        }

        // ── Master: Kendaraan ──
        $gesits = Vehicle::create([
            'user_id' => $hendra->id,
            'plate_number' => 'L 3021 KX',
            'brand' => 'Gesits',
            'model' => 'G1',
            'year' => 2023,
            'engine_number' => 'GS1-2309-88421',
            'chassis_number' => 'MH1G11120PJ088421',
            'fuel_type' => 'electric',
            'last_odometer' => 8450,
        ]);

        $vario = Vehicle::create([
            'user_id' => $hendra->id,
            'plate_number' => 'L 2847 UQ',
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'year' => 2022,
            'engine_number' => 'JE12E-1600-55123',
            'chassis_number' => 'MH1JM8112NK55123',
            'fuel_type' => 'gasoline',
            'last_odometer' => 4120,
        ]);

        $alva = Vehicle::create([
            'user_id' => $siti->id,
            'plate_number' => 'L 3158 ALV',
            'brand' => 'Alva',
            'model' => 'Cervo',
            'year' => 2023,
            'engine_number' => 'ACV-2311-10992',
            'chassis_number' => 'MH1ACV230PK10992',
            'fuel_type' => 'electric',
            'last_odometer' => 11230,
        ]);

        $beat = Vehicle::create([
            'user_id' => $citra->id,
            'plate_number' => 'L 4192 CP',
            'brand' => 'Honda',
            'model' => 'Beat Street',
            'year' => 2024,
            'engine_number' => 'JFP1E-2441-23981',
            'chassis_number' => 'MH1JFP112RK23981',
            'fuel_type' => 'gasoline',
            'last_odometer' => 4200,
        ]);

        ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $citra->id,
            'vehicle_id' => $beat->id,
            'customer_complaint' => 'Booking servis dari customer: motor terasa bergetar saat akselerasi.',
            'service_type' => 'regular',
            'odometer' => 4200,
            'location_type' => 'onsite',
            'status' => 'draft',
        ]);

        // ── Service Order 1: Antrian baru (draft) ──
        $wo1 = ServiceOrder::create([
            'wo_number' => 'WO-2610-0001',
            'customer_id' => $hendra->id,
            'vehicle_id' => $gesits->id,
            'sa_id' => $sa->id,
            'technician_id' => null,
            'customer_complaint' => 'Motor sering mati mendadak saat perjalanan jauh, lampu depan terasa redup.',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'status' => 'draft',
            'estimated_completion' => now()->addDay(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // ── Service Order 2: Sedang inspeksi (draft + lembar pemeriksaan) ──
        $wo2 = ServiceOrder::create([
            'wo_number' => 'WO-2610-0002',
            'customer_id' => $siti->id,
            'vehicle_id' => $alva->id,
            'sa_id' => $sa->id,
            'technician_id' => $budi->id,
            'customer_complaint' => 'Rem depan bunyi kresek-kresek saat ditekan, terasa kurang pakem.',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'status' => 'draft',
            'estimated_completion' => now()->addHours(6),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subHours(5),
        ]);

        Inspection::create([
            'service_order_id' => $wo2->id,
            'technician_id' => $budi->id,
            'checklist' => $this->checklist([
                'pengereman' => 'p', 'roda' => 'ok', 'mesin' => 'ok',
            ]),
            'findings' => 'Kampas rem depan tersisa ±2 mm, piringan rem bergelombang. Sisanya dalam kondisi baik.',
            'recommendations' => 'Ganti kampas rem depan + service piringan. Estimasi menyusul dari SA.',
        ]);

        $wo2->update(['status' => 'inspected']);

        // ── Service Order 3: Estimasi menunggu persetujuan ──
        $wo3 = ServiceOrder::create([
            'wo_number' => 'WO-2610-0003',
            'customer_id' => $hendra->id,
            'vehicle_id' => $vario->id,
            'sa_id' => $sa->id,
            'technician_id' => $agus->id,
            'customer_complaint' => 'Servis berkala 10.000 KM, motor terasa kasar dan bunyi CVT.',
            'sa_diagnosis' => 'Belt CVT mulai aus dan roller perlu dicek. Service berkala Jomoto gratis jasa.',
            'service_type' => 'free_jomoto',
            'location_type' => 'onsite',
            'status' => 'waiting_approval',
            'estimated_completion' => now()->addHours(8),
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHour(),
        ]);

        Inspection::create([
            'service_order_id' => $wo3->id,
            'technician_id' => $agus->id,
            'checklist' => $this->checklist([
                'sprocket' => 'p', 'mesin' => 'p',
            ]),
            'findings' => 'Belt CVT retak halus, roller sudah pipih di sisi luar. Oli mesin mulai kental.',
            'recommendations' => 'Ganti belt & roller set, ganti oli mesin. Service berkala 10.000 KM (jaminan jasa gratis Jomoto).',
        ]);

        $this->addItems($wo3, [
            ['service', 'Service Berkala 10.000 KM (Jomoto)', 1, 150000, true, 'Gratis ongkos jasa Jomoto 10.000 KM'],
            ['sparepart', 'Belt CVT Honda Vario 160 (HET)', 1, 145000, false, null],
            ['sparepart', 'Roller Set Vario 160 (HET)', 1, 98000, false, null],
            ['sparepart', 'Oli Mesin MPX 2 1L (HET)', 1, 78000, false, null],
            ['sub_material', 'Pembersihan Karburator', 1, 45000, false, null],
        ]);

        // ── Service Order 4: Sedang dikerjakan + timer berjalan ──
        $wo4 = ServiceOrder::create([
            'wo_number' => 'WO-2610-0004',
            'customer_id' => $hendra->id,
            'vehicle_id' => $gesits->id,
            'sa_id' => $sa->id,
            'technician_id' => $budi->id,
            'customer_complaint' => 'CVT bunyi kasar saat akselerasi, tenaga terasa drop.',
            'sa_diagnosis' => 'Roller aus dan belt melar. Setujui penggantian roller set + belt + jasa servis CVT.',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'status' => 'working',
            'estimated_completion' => now()->addHours(3),
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subMinutes(25),
        ]);

        Inspection::create([
            'service_order_id' => $wo4->id,
            'technician_id' => $budi->id,
            'checklist' => $this->checklist([
                'sprocket' => 'g', 'suspensi' => 'p',
            ]),
            'findings' => 'Roller CVT aus tidak simetris, belt sudah tipis. Suspensi belakang mulai lemah.',
            'recommendations' => 'Ganti roller set & belt CVT, lakukan servis CVT lengkap.',
        ]);

        $this->addItems($wo4, [
            ['service', 'Servis CVT / Roller', 1, 120000, false, null],
            ['sparepart', 'Roller Set Gesits G1 (HET)', 1, 185000, false, null],
            ['sparepart', 'Belt CVT Gesits G1 (HET)', 1, 145000, false, null],
            ['sub_material', 'Gerinda / Bubut / Las', 1, 75000, false, null],
        ]);

        WorkLog::create([
            'service_order_id' => $wo4->id,
            'technician_id' => $budi->id,
            'started_at' => now()->subHours(3),
            'stopped_at' => now()->subHours(2),
            'duration_seconds' => 3600,
            'notes' => 'Bongkar housing CVT & inspeksi roller',
        ]);

        WorkLog::create([
            'service_order_id' => $wo4->id,
            'technician_id' => $budi->id,
            'started_at' => now()->subMinutes(25),
            'stopped_at' => null,
            'duration_seconds' => 0,
            'notes' => 'Pemasangan roller & belt baru',
        ]);

        // ── Service Order 5: Selesai & invoiced (sudah dibayar QRIS) ──
        $wo5 = ServiceOrder::create([
            'wo_number' => 'WO-2610-0005',
            'customer_id' => $siti->id,
            'vehicle_id' => $alva->id,
            'sa_id' => $sa->id,
            'technician_id' => $agus->id,
            'customer_complaint' => 'Servis berkala + ganti oli, motor sudah 11.000 KM.',
            'sa_diagnosis' => 'Servis berkala lengkap, ganti oli mesin dan pelumasan CVT.',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'status' => 'invoiced',
            'estimated_completion' => now()->subDay()->addHours(4),
            'actual_completion' => now()->subDay()->addHours(3),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);

        Inspection::create([
            'service_order_id' => $wo5->id,
            'technician_id' => $agus->id,
            'checklist' => $this->checklist([
                'mesin' => 'p', 'roda' => 'p',
            ]),
            'findings' => 'Oli mesin sudah hitam kental, ban belakang sisa tapak 60%.',
            'recommendations' => 'Ganti oli mesin + pelumasan CVT. Ban masih layak pakai sampai 15.000 KM.',
        ]);

        WorkLog::create([
            'service_order_id' => $wo5->id,
            'technician_id' => $agus->id,
            'started_at' => now()->subDay()->addHours(1),
            'stopped_at' => now()->subDay()->addHours(3),
            'duration_seconds' => 7200,
            'notes' => 'Servis berkala lengkap + ganti oli',
        ]);

        $this->addItems($wo5, [
            ['service', 'Servis Berkala Lengkap', 1, 150000, false, null],
            ['sparepart', 'Oli Mesin Fully Synthetic 1L (HET)', 1, 135000, false, null],
            ['sub_material', 'Gemuk / Pelumasan Khusus', 1, 25000, false, null],
        ]);

        $invoice = Invoice::createFromOrder($wo5);
        $invoice->markPaid('qris');
        $wo5->update(['status' => 'invoiced']);

        Storage::disk('public')->put('inspections/demo-before.svg', $this->demoPhoto('Sebelum Perbaikan', '#f59e0b'));
        Storage::disk('public')->put('inspections/demo-after.svg', $this->demoPhoto('Sesudah Perbaikan', '#84cc16'));

        $homeOrder = ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $citra->id,
            'vehicle_id' => $beat->id,
            'sa_id' => $sa->id,
            'technician_id' => $budi->id,
            'customer_complaint' => 'Servis berkala 4.000 KM, suara rem depan mulai berdecit.',
            'sa_diagnosis' => 'Klaim servis gratis ATPM. Kampas rem depan perlu diganti; teknisi datang ke rumah.',
            'service_type' => 'free_atpm',
            'coupon_milestone' => 4000,
            'odometer' => 4200,
            'location_type' => 'offsite',
            'offsite_address' => 'Jl. Pucang Anom Timur No. 18, Surabaya',
            'location_notes' => 'Rumah pagar hitam di samping minimarket',
            'visit_fee' => 50000,
            'status' => 'invoiced',
            'actual_completion' => now()->subHours(4),
            'created_at' => now()->subHours(8),
            'updated_at' => now()->subHours(4),
        ]);
        $this->addItems($homeOrder, [
            ['service', 'Servis Berkala 4.000 KM (ATPM)', 1, 150000, true, 'Jasa gratis kupon ATPM 4.000 KM'],
            ['sparepart', 'Oli Mesin — Kupon ATPM', 1, 78000, true, 'Oli gratis ATPM 4.000 KM'],
            ['sparepart', 'Sparepart Dasar — Kupon ATPM', 1, 25000, true, 'Sparepart dasar gratis ATPM 4.000 KM'],
            ['sparepart', 'Kampas Rem Depan Honda (HET)', 1, 85000, false, null],
        ]);
        Inspection::create([
            'service_order_id' => $homeOrder->id,
            'technician_id' => $budi->id,
            'checklist' => $this->checklist(['pengereman' => 'p', 'mesin' => 'p']),
            'findings' => 'Oli mencapai jadwal penggantian, kampas rem depan mulai menipis.',
            'recommendations' => 'Penggantian oli ditanggung kupon ATPM; kampas rem disetujui customer.',
            'before_photos' => ['inspections/demo-before.svg'],
            'after_photos' => ['inspections/demo-after.svg'],
        ]);
        $homeInvoice = Invoice::createFromOrder($homeOrder);
        $homeInvoice->markPaid('cash');

        $transferOrder = ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $siti->id,
            'vehicle_id' => $alva->id,
            'sa_id' => $sa->id,
            'technician_id' => $agus->id,
            'customer_complaint' => 'Servis berkala 10.000 KM dan pemeriksaan baterai.',
            'sa_diagnosis' => 'Jasa berkala gratis Jomoto; filter dan cairan perawatan dibayar.',
            'service_type' => 'free_jomoto',
            'coupon_milestone' => 10000,
            'odometer' => 11230,
            'location_type' => 'onsite',
            'status' => 'invoiced',
            'actual_completion' => now()->subDays(3),
            'created_at' => now()->subDays(3)->subHours(3),
            'updated_at' => now()->subDays(3),
        ]);
        $this->addItems($transferOrder, [
            ['service', 'Servis Berkala 10.000 KM (Jomoto)', 1, 150000, true, 'Jasa gratis kupon Jomoto 10.000 KM'],
            ['sparepart', 'Filter Udara Alva Cervo (HET)', 1, 95000, false, null],
            ['sub_material', 'Pembersihan & Kalibrasi Throttle Body', 1, 45000, false, null],
        ]);
        $transferInvoice = Invoice::createFromOrder($transferOrder);
        $transferInvoice->markPaid('transfer');

        $cashierOrder = ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $citra->id,
            'vehicle_id' => $beat->id,
            'sa_id' => $sa->id,
            'technician_id' => $agus->id,
            'customer_complaint' => 'Servis berkala dan pengecekan rem.',
            'sa_diagnosis' => 'Servis berkala ringan.',
            'service_type' => 'regular',
            'location_type' => 'onsite',
            'status' => 'invoiced',
            'actual_completion' => now()->subHour(),
        ]);
        $this->addItems($cashierOrder, [
            ['service', 'Servis Berkala Ringan', 1, 85000, false, null],
        ]);
        Invoice::createFromOrder($cashierOrder);

        unset($admin);
    }

    /**
     * 68 item pekerjaan layanan: [kategori, nama, harga P, harga S, harga G]. Harga 0 = '-'.
     *
     * @return array<int, array{0: string, 1: string, 2: int, 3: int, 4: int}>
     */
    private function serviceCatalog(): array
    {
        return [
            // ── Mesin (12) ──
            ['mesin', 'Tune Up Mesin', 0, 175000, 0],
            ['mesin', 'Bongkar Pasang Blok Silinder', 250000, 0, 0],
            ['mesin', 'Perbaikan Piston & Seher', 175000, 0, 0],
            ['mesin', 'Ganti Piston Ring', 0, 0, 300000],
            ['mesin', 'Perapian / Ganti Klep', 85000, 65000, 150000],
            ['mesin', 'Servis Karburator / Throttle Body', 95000, 75000, 0],
            ['mesin', 'Ganti Oli Mesin + Filter', 0, 65000, 0],
            ['mesin', 'Ganti Busi', 0, 25000, 30000],
            ['mesin', 'Perbaikan Dinamo Starter', 150000, 0, 0],
            ['mesin', 'Ganti Gasket / Packing Mesin', 0, 0, 125000],
            ['mesin', 'Perbaikan Engine Mounting', 65000, 30000, 150000],
            ['mesin', 'Perbaikan Cam Chain / Tensioner', 75000, 50000, 120000],

            // ── Kelistrikan (10) ──
            ['kelistrikan', 'Servis Kelistrikan Umum', 0, 100000, 0],
            ['kelistrikan', 'Perbaikan Alternator / Kapasitor', 135000, 0, 0],
            ['kelistrikan', 'Ganti Alternator', 0, 0, 475000],
            ['kelistrikan', 'Perbaikan Coil Pengapian', 95000, 0, 0],
            ['kelistrikan', 'Ganti Coil', 0, 0, 250000],
            ['kelistrikan', 'Perbaikan CDI / ECU', 150000, 0, 0],
            ['kelistrikan', 'Perbaikan Kabel & Soket', 35000, 0, 0],
            ['kelistrikan', 'Servis Aki / Baterai', 25000, 30000, 35000],
            ['kelistrikan', 'Perbaikan Lampu & Sein', 45000, 25000, 75000],
            ['kelistrikan', 'Perbaikan Saklar & Klakson', 40000, 25000, 85000],

            // ── Sistem Pengereman (8) ──
            ['pengereman', 'Servis Rem Depan', 50000, 45000, 0],
            ['pengereman', 'Ganti Kampas Rem Depan', 0, 0, 65000],
            ['pengereman', 'Servis Rem Belakang', 50000, 45000, 0],
            ['pengereman', 'Ganti Kampas Rem Belakang', 0, 0, 65000],
            ['pengereman', 'Perbaikan / Lolos Piringan Cakram', 75000, 40000, 175000],
            ['pengereman', 'Perbaikan Kaliper & Piston Rem', 85000, 50000, 0],
            ['pengereman', 'Ganti Minyak Rem & Bleeding', 0, 45000, 0],
            ['pengereman', 'Perbaikan Selang & Master Rem', 65000, 0, 120000],

            // ── Roda (8) ──
            ['roda', 'Spooring / Setel Roda', 0, 50000, 0],
            ['roda', 'Ganti Ban Depan', 0, 35000, 0],
            ['roda', 'Ganti Ban Belakang', 0, 40000, 0],
            ['roda', 'Perbaikan Ban Bocor (Tambal)', 25000, 0, 0],
            ['roda', 'Perbaikan Velg (Press / Las)', 75000, 0, 350000],
            ['roda', 'Ganti Tubeless Sealant', 0, 30000, 0],
            ['roda', 'Perbaikan Bearing Roda', 55000, 0, 95000],
            ['roda', 'Perbaikan / Ganti Tutup Pentil & Klep', 20000, 0, 45000],

            // ── Suspensi (8) ──
            ['suspensi', 'Servis Shock Depan', 65000, 55000, 0],
            ['suspensi', 'Ganti Shockbreaker Depan', 0, 40000, 150000],
            ['suspensi', 'Servis Shock Belakang', 65000, 55000, 0],
            ['suspensi', 'Ganti Shockbreaker Belakang', 0, 40000, 175000],
            ['suspensi', 'Perbaikan / Ganti Segitiga & Conset', 95000, 50000, 225000],
            ['suspensi', 'Perbaikan / Ganti Tabung Shock', 125000, 0, 250000],
            ['suspensi', 'Perbaikan / Ganti Karet Bushing', 45000, 0, 85000],
            ['suspensi', 'Servis Link Stabilizer / Penyangga', 40000, 35000, 95000],

            // ── Sistem Pendingin (7) ──
            ['pendingin', 'Servis Radiator & Flushing', 65000, 75000, 0],
            ['pendingin', 'Ganti Air Radiator', 0, 45000, 0],
            ['pendingin', 'Perbaikan / Ganti Pompa Air', 95000, 0, 275000],
            ['pendingin', 'Perbaikan Selang Radiator', 45000, 0, 95000],
            ['pendingin', 'Ganti Thermostat', 0, 40000, 125000],
            ['pendingin', 'Perbaikan / Ganti Kipas Radiator', 75000, 35000, 175000],
            ['pendingin', 'Perbaikan Bocor Blok / Silinder Air', 175000, 0, 0],

            // ── Sistem Kemudi (6) ──
            ['kemudi', 'Servis Setang & Steering Head', 55000, 45000, 0],
            ['kemudi', 'Perbaikan / Ganti Karet Grip', 0, 25000, 45000],
            ['kemudi', 'Perbaikan / Ganti Kabel Kopling Setang', 45000, 35000, 85000],
            ['kemudi', 'Perbaikan / Ganti Tuas Rem & Kopling', 50000, 30000, 95000],
            ['kemudi', 'Perbaikan / Ganti Spion', 35000, 0, 75000],
            ['kemudi', 'Perbaikan / Ganti Batang Setang (Stang)', 75000, 0, 175000],

            // ── Sprocket & Rantai (9) ──
            ['sprocket', 'Servis & Pelumasan Rantai', 0, 35000, 0],
            ['sprocket', 'Ganti Rantai Sprocket Set', 0, 65000, 0],
            ['sprocket', 'Perbaikan / Ganti Gear Sprocket Belakang', 55000, 40000, 85000],
            ['sprocket', 'Perbaikan / Ganti Gear Sprocket Depan', 55000, 40000, 85000],
            ['sprocket', 'Perbaikan / Ganti Tensioner Rantai', 45000, 0, 95000],
            ['sprocket', 'Perbaikan / Ganti Roller CVT', 65000, 50000, 0],
            ['sprocket', 'Ganti Belt CVT / V-Belt', 0, 55000, 0],
            ['sprocket', 'Servis / Bongkar Housing CVT', 95000, 75000, 0],
            ['sprocket', 'Perbaikan / Ganti Slider & Guide Rantai', 45000, 30000, 85000],
        ];
    }

    private function checklist(array $overrides = []): array
    {
        $labels = ServiceOrder::INSPECTION_CHECKLIST;

        return collect($labels)
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'status' => $overrides[$key] ?? 'ok',
            ])
            ->values()
            ->all();
    }

    private function addItems(ServiceOrder $order, array $rows): void
    {
        foreach ($rows as [$type, $name, $qty, $price, $free, $reason]) {
            $order->items()->create([
                'item_type' => $type,
                'item_name' => $name,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $free ? 0 : $qty * $price,
                'is_free' => $free,
                'free_reason' => $free ? $reason : null,
            ]);
        }
    }

    private function demoPhoto(string $label, string $accent): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="720" height="440" viewBox="0 0 720 440">
<rect width="720" height="440" rx="32" fill="#18181b"/><path d="M0 320h720v120H0z" fill="#27272a"/>
<circle cx="220" cy="300" r="66" fill="none" stroke="#a1a1aa" stroke-width="16"/><circle cx="510" cy="300" r="66" fill="none" stroke="#a1a1aa" stroke-width="16"/>
<path d="m220 300 96-112 84 112H220l62-92h109l119 92m-190-112h103" fill="none" stroke="{$accent}" stroke-linecap="round" stroke-linejoin="round" stroke-width="22"/>
<text x="360" y="82" fill="#fff" font-family="Arial,sans-serif" font-size="28" font-weight="700" text-anchor="middle">JOMOTO · FOTO DEMO</text>
<text x="360" y="390" fill="{$accent}" font-family="Arial,sans-serif" font-size="26" font-weight="700" text-anchor="middle">{$label}</text>
</svg>
SVG;
    }
}
