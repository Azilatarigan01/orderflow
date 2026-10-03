<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');                    // submitted, approved, rejected, revision, po_issued, gr_created, etc.
            $table->string('entity_type');               // PurchaseRequest, PurchaseOrder, GoodsReceipt
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_label');              // PR-202609-0001
            $table->json('before_state')->nullable();    // {"status": "submitted"}
            $table->json('after_state')->nullable();     // {"status": "approved"}
            $table->text('description');                 // "Approved purchase request PR-202609-0001"
            $table->text('comment')->nullable();         // Komentar approver
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — audit trail is immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
