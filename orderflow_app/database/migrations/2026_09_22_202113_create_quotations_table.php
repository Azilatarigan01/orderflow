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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->string('quotation_number', 100)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->unsignedInteger('estimated_delivery_days')->default(1);
            $table->unsignedInteger('warranty_months')->default(0);
            $table->string('warranty_info', 150)->nullable();
            $table->date('valid_until')->nullable();
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->text('selection_reason')->nullable();
            $table->boolean('is_single_source')->default(false);
            $table->text('single_source_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
