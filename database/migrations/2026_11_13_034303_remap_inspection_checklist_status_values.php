<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** attention -> p (perbaikan), broken -> g (ganti). */
    private const MAP = ['attention' => 'p', 'broken' => 'g'];

    public function up(): void
    {
        DB::table('inspections')->orderBy('id')->chunkById(100, function ($inspections): void {
            foreach ($inspections as $inspection) {
                $checklist = json_decode($inspection->checklist, true);
                if (! is_array($checklist)) {
                    continue;
                }

                $changed = false;
                foreach ($checklist as $index => $item) {
                    $status = $item['status'] ?? null;
                    if (isset(self::MAP[$status])) {
                        $checklist[$index]['status'] = self::MAP[$status];
                        $changed = true;
                    }
                }

                if ($changed) {
                    DB::table('inspections')->where('id', $inspection->id)
                        ->update(['checklist' => json_encode($checklist)]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('inspections')->orderBy('id')->chunkById(100, function ($inspections): void {
            foreach ($inspections as $inspection) {
                $checklist = json_decode($inspection->checklist, true);
                if (! is_array($checklist)) {
                    continue;
                }

                $changed = false;
                foreach ($checklist as $index => $item) {
                    $status = $item['status'] ?? null;
                    $legacy = array_search($status, self::MAP, true);
                    if ($legacy !== false) {
                        $checklist[$index]['status'] = $legacy;
                        $changed = true;
                    }
                }

                if ($changed) {
                    DB::table('inspections')->where('id', $inspection->id)
                        ->update(['checklist' => json_encode($checklist)]);
                }
            }
        });
    }
};
