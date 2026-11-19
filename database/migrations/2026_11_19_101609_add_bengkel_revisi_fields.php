<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('address')->nullable()->after('whatsapp');
            });
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'transmission')) {
                $table->string('transmission', 50)->nullable()->after('year');
            }
            if (! Schema::hasColumn('vehicles', 'engine_type')) {
                $table->string('engine_type', 100)->nullable()->after('fuel_type');
            }
            if (! Schema::hasColumn('vehicles', 'serial_number')) {
                $table->string('serial_number', 100)->nullable()->after('chassis_number');
            }
        });

        Schema::table('service_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('service_orders', 'complaint_category')) {
                $table->string('complaint_category', 100)->nullable()->after('customer_complaint');
            }
            if (! Schema::hasColumn('service_orders', 'visit_date')) {
                $table->date('visit_date')->nullable()->after('location_type');
            }
        });

        Schema::table('inspections', function (Blueprint $table) {
            if (! Schema::hasColumn('inspections', 'sa_name')) {
                $table->string('sa_name', 100)->nullable()->after('manager_name');
            }
            if (! Schema::hasColumn('inspections', 'exterior_photos')) {
                $table->json('exterior_photos')->nullable()->after('exterior_checklist');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(['sa_name', 'exterior_photos']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['complaint_category', 'visit_date']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['transmission', 'engine_type', 'serial_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address']);
        });
    }
};
