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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dateTime('rfq_deadline')->nullable()->after('required_date');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('submission_status', 50)->default('on_time')->after('valid_until');
            $table->text('late_dispensation_reason')->nullable()->after('submission_status');
            $table->string('single_source_category', 50)->nullable()->after('is_single_source');
            $table->string('single_source_memo_number', 100)->nullable()->after('single_source_category');
            $table->string('single_source_approver_name', 150)->nullable()->after('single_source_memo_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('rfq_deadline');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'submission_status',
                'late_dispensation_reason',
                'single_source_category',
                'single_source_memo_number',
                'single_source_approver_name',
            ]);
        });
    }
};
