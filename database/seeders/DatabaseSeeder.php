<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;

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
            'name' => 'Agus Prasetyo',
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
                'rem_depan' => 'attention', 'ban_depan' => 'ok', 'oli' => 'ok',
                'body_cat' => 'ok', 'kelengkapan' => 'ok',
            ]),
            'findings' => 'Kampas rem depan tersisa ±2 mm, piringan rem bergelombang. Sisanya dalam kondisi baik.',
            'recommendations' => 'Ganti kampas rem depan + service piringan. Estimasi menyusul dari SA.',
        ]);

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
                'rantai_belt' => 'attention', 'oli' => 'attention',
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
                'rantai_belt' => 'broken', 'suspensi' => 'attention',
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
                'oli' => 'attention', 'ban_belakang' => 'attention',
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

        unset($admin);
    }

    private function checklist(array $overrides = []): array
    {
        $labels = [
            'rem_depan' => 'Rem Depan',
            'rem_belakang' => 'Rem Belakang',
            'ban_depan' => 'Ban Depan',
            'ban_belakang' => 'Ban Belakang',
            'lampu_depan' => 'Lampu Depan',
            'lampu_belakang' => 'Lampu Belakang & Sein',
            'klakson' => 'Klakson',
            'aki' => 'Aki / Baterai',
            'oli' => 'Oli Mesin',
            'rantai_belt' => 'Rantai / Belt CVT',
            'suspensi' => 'Suspensi Depan & Belakang',
            'body_cat' => 'Body & Cat',
            'kelengkapan' => 'STNK & Kelengkapan',
        ];

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
}
