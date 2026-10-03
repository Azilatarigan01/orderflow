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
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('termin_name', 100)->nullable()->after('receipt_type');
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('termin_name');
            $table->decimal('cumulative_progress_percentage', 5, 2)->default(0)->after('progress_percentage');
            $table->decimal('nominal_claimed', 15, 2)->default(0)->after('cumulative_progress_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'termin_name',
                'progress_percentage',
                'cumulative_progress_percentage',
                'nominal_claimed',
            ]);
        });
    }
};
