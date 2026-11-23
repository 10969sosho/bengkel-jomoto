<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('service_orders', 'booking_date')) {
                $table->date('booking_date')->nullable()->after('visit_date');
            }
            if (! Schema::hasColumn('service_orders', 'need_tax_invoice')) {
                $table->boolean('need_tax_invoice')->default(false)->after('booking_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'need_tax_invoice']);
        });
    }
};
