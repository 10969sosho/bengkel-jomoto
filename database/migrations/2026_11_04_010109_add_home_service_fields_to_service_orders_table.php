<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->text('offsite_address')->nullable();
            $table->string('location_notes')->nullable();
            $table->unsignedInteger('visit_fee')->default(0);
            $table->unsignedInteger('odometer')->nullable();
            $table->unsignedInteger('coupon_milestone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['offsite_address', 'location_notes', 'visit_fee', 'odometer', 'coupon_milestone']);
        });
    }
};
