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
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->char('service_action', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('service_action');
        });
    }
};
