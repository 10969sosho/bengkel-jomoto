<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Status WO yang didukung alur servis, termasuk pemeriksaan akhir SA & konfirmasi hasil customer. */
    private const STATUSES = [
        'draft', 'waiting_approval', 'approved', 'working', 'completed',
        'waiting_confirmation', 'confirmed', 'invoiced', 'cancelled',
    ];

    private const ORIGINAL_STATUSES = [
        'draft', 'waiting_approval', 'approved', 'working', 'completed', 'invoiced', 'cancelled',
    ];

    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->enum('status', self::STATUSES)->default('draft')->change();
            $table->json('requested_services')->nullable()->after('customer_complaint');
            $table->string('stnk_photo')->nullable()->after('requested_services');
            $table->dateTime('estimate_approved_at')->nullable()->after('actual_completion');
            $table->dateTime('final_check_at')->nullable()->after('estimate_approved_at');
            $table->text('final_check_notes')->nullable()->after('final_check_at');
            $table->dateTime('result_confirmed_at')->nullable()->after('final_check_notes');
            $table->text('result_confirmation_notes')->nullable()->after('result_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->enum('status', self::ORIGINAL_STATUSES)->default('draft')->change();
            $table->dropColumn([
                'requested_services', 'stnk_photo', 'estimate_approved_at',
                'final_check_at', 'final_check_notes', 'result_confirmed_at', 'result_confirmation_notes',
            ]);
        });
    }
};
