<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class JomotoCustomerSyncService
{
    private const CONNECTION = 'jomoto_main';

    /**
     * Sinkronkan user role "buyer" dari database website jomotocenter.com
     * ke tabel users bengkel dengan role "customer".
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function sync(): array
    {
        $this->assertConnectionReady();

        $columns = Schema::connection(self::CONNECTION)->getColumnListing('users');
        $phoneColumn = $this->pickColumn($columns, ['phone', 'phone_number', 'mobile', 'no_hp']);
        $addressColumn = $this->pickColumn($columns, ['address', 'alamat']);

        $rows = DB::connection(self::CONNECTION)
            ->table('users')
            ->where('role', 'buyer')
            ->orderBy('id')
            ->get();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (empty($row->email)) {
                $skipped++;

                continue;
            }

            $existing = User::query()
                ->where(function ($query) use ($row): void {
                    $query->where('ecommerce_user_id', $row->id)
                        ->orWhere('email', $row->email);
                })
                ->first();

            $attributes = [
                'name' => filled($row->name ?? null) ? $row->name : $row->email,
                'phone' => $phoneColumn ? ($row->{$phoneColumn} ?? null) : null,
                'address' => $addressColumn ? ($row->{$addressColumn} ?? null) : null,
            ];

            if ($existing) {
                // Jangan ambil alih akun bengkel yang bukan customer (mis. admin/sa).
                if ($existing->role !== 'customer') {
                    $skipped++;

                    continue;
                }

                $existing->update($attributes + [
                    'role' => 'customer',
                    'ecommerce_user_id' => $existing->ecommerce_user_id ?? $row->id,
                    'password' => filled($row->password ?? null) ? $row->password : $existing->password,
                ]);
                $updated++;

                continue;
            }

            User::create($attributes + [
                'email' => $row->email,
                'password' => filled($row->password ?? null) ? $row->password : Str::random(40),
                'role' => 'customer',
                'ecommerce_user_id' => $row->id,
            ]);
            $created++;
        }

        return compact('created', 'updated', 'skipped');
    }

    private function assertConnectionReady(): void
    {
        if (! config('database.connections.'.self::CONNECTION)) {
            throw new RuntimeException('Konfigurasi database jomoto_main belum tersedia.');
        }

        if (! Schema::connection(self::CONNECTION)->hasTable('users')) {
            throw new RuntimeException('Tabel users tidak ditemukan di database jomotocenter. Periksa kredensial JOMOTO_DB_* di .env.');
        }
    }

    /**
     * @param  list<string>  $candidates
     */
    private function pickColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
