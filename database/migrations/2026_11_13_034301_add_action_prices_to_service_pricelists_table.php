<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_pricelists', function (Blueprint $table) {
            $table->decimal('price_p', 12, 2)->default(0)->after('price');
            $table->decimal('price_s', 12, 2)->default(0)->after('price_p');
            $table->decimal('price_g', 12, 2)->default(0)->after('price_s');
        });
    }

    public function down(): void
    {
        Schema::table('service_pricelists', function (Blueprint $table) {
            $table->dropColumn(['price_p', 'price_s', 'price_g']);
        });
    }
};
