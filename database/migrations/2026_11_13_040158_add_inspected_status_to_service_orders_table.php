<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'draft', 'inspected', 'waiting_approval', 'approved', 'working', 'completed',
        'waiting_confirmation', 'confirmed', 'invoiced', 'cancelled',
    ];

    private const PREVIOUS_STATUSES = [
        'draft', 'waiting_approval', 'approved', 'working', 'completed',
        'waiting_confirmation', 'confirmed', 'invoiced', 'cancelled',
    ];

    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->enum('status', self::STATUSES)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->enum('status', self::PREVIOUS_STATUSES)->default('draft')->change();
        });
    }
};
