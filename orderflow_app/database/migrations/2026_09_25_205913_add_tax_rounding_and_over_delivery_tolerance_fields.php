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
            $table->decimal('tax_rate', 5, 2)->default(11.00)->after('subtotal');
            $table->string('tax_calculation_mode', 30)->default('line_item')->after('tax_rate');
            $table->decimal('tax_rounding_tolerance', 10, 2)->default(100.00)->after('tax_calculation_mode');
            $table->decimal('tax_rounding_difference', 10, 2)->default(0.00)->after('tax_amount');
            $table->decimal('over_delivery_tolerance_percentage', 5, 2)->default(5.00)->after('grand_total');
        });

        Schema::table('po_items', function (Blueprint $table) {
            $table->decimal('over_delivery_tolerance_percentage', 5, 2)->default(5.00)->after('received_quantity');
            $table->decimal('over_delivered_quantity', 10, 2)->default(0.00)->after('over_delivery_tolerance_percentage');
        });

        Schema::table('gr_items', function (Blueprint $table) {
            $table->boolean('is_over_delivery')->default(false)->after('quantity_accepted');
            $table->decimal('over_delivery_quantity', 10, 2)->default(0.00)->after('is_over_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate',
                'tax_calculation_mode',
                'tax_rounding_tolerance',
                'tax_rounding_difference',
                'over_delivery_tolerance_percentage',
            ]);
        });

        Schema::table('po_items', function (Blueprint $table) {
            $table->dropColumn([
                'over_delivery_tolerance_percentage',
                'over_delivered_quantity',
            ]);
        });

        Schema::table('gr_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_over_delivery',
                'over_delivery_quantity',
            ]);
        });
    }
};
