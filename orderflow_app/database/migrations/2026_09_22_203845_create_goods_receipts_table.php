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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('gr_number', 30)->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('receipt_type', ['goods', 'service'])->default('goods');
            $table->date('received_date');
            $table->string('delivery_note_no', 100)->nullable();
            $table->string('delivery_note_doc')->nullable();
            $table->string('item_condition', 100)->default('Baik (100% OK)');
            $table->text('inspection_notes')->nullable();
            $table->enum('status', ['partially_received', 'completed', 'disputed'])->default('completed');

            // Service Acceptance / BAST specific fields
            $table->date('service_period_start')->nullable();
            $table->date('service_period_end')->nullable();
            $table->text('service_deliverables')->nullable();
            $table->string('acceptance_approver_name', 150)->nullable();
            $table->string('bast_document_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
