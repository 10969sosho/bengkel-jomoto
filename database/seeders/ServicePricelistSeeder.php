<?php

namespace Database\Seeders;

use App\Models\ServicePricelist;
use Illuminate\Database\Seeder;

class ServicePricelistSeeder extends Seeder
{
    public function run(): void
    {
        // Ini adalah pricelist lengkap 36 item dengan 8 kategori sesuai permintaan
        // Kategori: mesin, kelistrikan, pengereman, roda, suspensi, pendingin, kemudi, sprocket

        // ── Mesin (10 items) ──
        $pricelist = [
            // Mesin
            ['mesin', 'Oli Mesin', 0, 0, 60000],
            ['mesin', 'Oli Transmisi/Gardan', 0, 0, 60000],
            ['mesin', 'Filter Oli', 0, 20000, 30000],
            ['mesin', 'Putaran Mesin', 0, 50000, 200000],
            ['mesin', 'Karburator / Throttle Body & Injection', 100000, 150000, 100000],
            ['mesin', 'Filter Udara', 0, 50000, 50000],
            ['mesin', 'Busi', 0, 50000, 50000],
            ['mesin', 'Klep', 150000, 200000, 400000],
            ['mesin', 'CVT', 50000, 200000, 300000],

            // Kelistrikan
            ['kelistrikan', 'Komponen Kelistrikan', 150000, 0, 200000],
            ['kelistrikan', 'Baterai', 200000, 0, 200000],
            ['kelistrikan', 'Kabel', 200000, 0, 200000],
            ['kelistrikan', 'Sekring', 100000, 0, 100000],

            // Sistem Pengereman
            ['pengereman', 'Cakram Rem', 50000, 100000, 100000],
            ['pengereman', 'Kampas Rem', 50000, 100000, 100000],
            ['pengereman', 'Minyak Rem', 200000, 0, 200000],
            ['pengereman', 'Selang Rem', 150000, 0, 150000],
            ['pengereman', 'Pengereman Depan', 50000, 50000, 100000],
            ['pengereman', 'Pengereman Belakang', 50000, 50000, 100000],
            ['pengereman', 'Pedal Rem', 50000, 50000, 50000],
            ['pengereman', 'Tuas Rem', 100000, 0, 100000],

            // Roda
            ['roda', 'Ban', 0, 0, 100000],
            ['roda', 'Tekanan Ban', 0, 0, 0],
            ['roda', 'Laher Roda', 0, 0, 100000],
            ['roda', 'Velg', 0, 0, 100000],

            // Suspensi
            ['suspensi', 'Perawatan Sok Absorber Depan', 0, 150000, 100000],
            ['suspensi', 'Perawatan Sok Absorber Belakang', 0, 150000, 100000],

            // Sistem Pendingin
            ['pendingin', 'Cairan Pendingin', 0, 0, 50000],
            ['pendingin', 'Kipas Radiator', 0, 0, 100000],
            ['pendingin', 'Selang', 0, 0, 100000],

            // Sistem Kemudi
            ['kemudi', 'Steering Bearing', 0, 0, 200000],

            // Sprocket dan Rantai
            ['sprocket', 'Pelumasan Rantai', 0, 0, 0],
            ['sprocket', 'Rantai', 0, 0, 50000],
            ['sprocket', 'Sprocket Belakang', 0, 0, 50000],
            ['sprocket', 'Sprocket Depan', 0, 0, 50000],
            ['sprocket', 'Pelindung Rantai', 0, 0, 0],
        ];

        ServicePricelist::truncate();

        foreach ($pricelist as [$category, $name, $price_p, $price_s, $price_g]) {
            ServicePricelist::create([
                'service_name' => $name,
                'category' => $category,
                'price' => $price_p ?: ($price_s ?: $price_g),
                'price_p' => $price_p,
                'price_s' => $price_s,
                'price_g' => $price_g,
                'is_active' => true,
            ]);
        }
    }
}