<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sa_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('customer_complaint');
            $table->text('sa_diagnosis')->nullable();
            $table->enum('service_type', ['regular', 'free_atpm', 'free_jomoto', 'home_service'])->default('regular');
            $table->enum('location_type', ['onsite', 'offsite'])->default('onsite');
            $table->enum('status', [
                'draft', 'waiting_approval', 'approved', 'working', 'completed', 'invoiced', 'cancelled',
            ])->default('draft')->index();
            $table->dateTime('estimated_completion')->nullable();
            $table->dateTime('actual_completion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
