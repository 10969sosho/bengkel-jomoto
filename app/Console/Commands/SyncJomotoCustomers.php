<?php

namespace App\Console\Commands;

use App\Services\JomotoCustomerSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncJomotoCustomers extends Command
{
    protected $signature = 'jomoto:sync-customers';

    protected $description = 'Sinkronisasi customer role buyer dari jomotocenter.com ke users bengkel (role customer)';

    public function handle(JomotoCustomerSyncService $sync): int
    {
        try {
            $result = $sync->sync();
        } catch (Throwable $exception) {
            $this->error('Sinkronisasi gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sinkronisasi selesai — {$result['created']} customer baru, {$result['updated']} diperbarui, {$result['skipped']} dilewati.");

        return self::SUCCESS;
    }
}
