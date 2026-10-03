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
        // 1. Master Data Measurement Units (UoM)
        Schema::create('measurement_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. BOX, RIM, PCS, UNIT, ROLL, MTR, KG, LTR, PKT
            $table->string('name', 50);          // e.g. Box / Kotak, Rim (500 Lembar), Pcs / Buah
            $table->string('category', 30);      // packaging, count, length, weight, volume, service
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Unit Conversions (e.g., 1 BOX = 5 RIM, 1 ROLL = 50 MTR, 1 KARTON = 24 PCS)
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('from_unit_code', 20);
            $table->string('to_unit_code', 20);
            $table->decimal('multiplier', 12, 4); // 1 from_unit = multiplier to_unit
            $table->string('notes', 150)->nullable();
            $table->timestamps();

            $table->unique(['from_unit_code', 'to_unit_code']);
        });

        // 3. Add UoM conversion tracking fields to gr_items
        Schema::table('gr_items', function (Blueprint $table) {
            $table->string('received_unit', 30)->nullable()->after('po_item_id');
            $table->decimal('raw_quantity_received', 12, 2)->nullable()->after('received_unit');
            $table->decimal('raw_quantity_rejected', 12, 2)->nullable()->after('raw_quantity_received');
            $table->decimal('conversion_multiplier', 12, 4)->default(1.0000)->after('raw_quantity_rejected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gr_items', function (Blueprint $table) {
            $table->dropColumn([
                'received_unit',
                'raw_quantity_received',
                'raw_quantity_rejected',
                'conversion_multiplier',
            ]);
        });

        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('measurement_units');
    }
};
