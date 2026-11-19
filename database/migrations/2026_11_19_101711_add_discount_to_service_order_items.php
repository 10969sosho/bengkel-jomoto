<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_order_items', 'discount')) {
            Schema::table('service_order_items', function (Blueprint $table) {
                $table->decimal('discount', 12, 2)->default(0)->after('unit_price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropColumn(['discount']);
        });
    }
};
