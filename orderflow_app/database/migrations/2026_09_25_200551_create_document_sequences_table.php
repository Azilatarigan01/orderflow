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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 20); // 'PR', 'PO', 'GR', 'BAST'
            $table->string('scope_code', 20)->default('GEN'); // e.g. 'IT', 'PROC', 'FIN', 'WH'
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedInteger('current_number')->default(0);
            $table->timestamps();

            $table->unique(['document_type', 'scope_code', 'period_year', 'period_month'], 'doc_seq_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
