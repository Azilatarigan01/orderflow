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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('purchase_orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('purchase_orders', 'default_notice_sent_at')) {
                $table->dateTime('default_notice_sent_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('purchase_orders', 'default_notice_count')) {
                $table->unsignedInteger('default_notice_count')->default(0)->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $cols = ['cancelled_at', 'cancellation_reason', 'default_notice_sent_at', 'default_notice_count'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
