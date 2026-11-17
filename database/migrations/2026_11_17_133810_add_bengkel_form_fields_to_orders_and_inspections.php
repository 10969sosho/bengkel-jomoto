<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('estimate_number', 50)->nullable()->after('wo_number');
            $table->string('spk_number', 50)->nullable()->after('estimate_number');
            $table->dateTime('spk_issued_at')->nullable()->after('estimate_approved_at');
            $table->string('work_start_time', 20)->nullable()->after('spk_issued_at');
            $table->decimal('estimate_discount', 12, 2)->default(0)->after('work_start_time');
            $table->decimal('estimate_tax', 12, 2)->default(0)->after('estimate_discount');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->string('inspection_number', 50)->nullable()->after('id');
            $table->date('inspection_date')->nullable()->after('inspection_number');
            $table->string('manager_name', 100)->nullable()->default('ARTI DEWI')->after('inspection_date');
            $table->string('odometer_photo')->nullable()->after('manager_name');
            $table->string('fuel_photo')->nullable()->after('odometer_photo');
            $table->string('fuel_level', 20)->nullable()->after('fuel_photo');
            $table->json('documents_checklist')->nullable()->after('fuel_level');
            $table->json('exterior_checklist')->nullable()->after('documents_checklist');
            $table->dateTime('mechanic_confirmed_at')->nullable()->after('exterior_checklist');
            $table->text('mechanic_notes')->nullable()->after('mechanic_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn([
                'inspection_number',
                'inspection_date',
                'manager_name',
                'odometer_photo',
                'fuel_photo',
                'fuel_level',
                'documents_checklist',
                'exterior_checklist',
                'mechanic_confirmed_at',
                'mechanic_notes',
            ]);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn([
                'estimate_number',
                'spk_number',
                'spk_issued_at',
                'work_start_time',
                'estimate_discount',
                'estimate_tax',
            ]);
        });
    }
};
