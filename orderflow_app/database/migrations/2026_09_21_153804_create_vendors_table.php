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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('category', 100);
            $table->string('contact_person', 100);
            $table->string('email', 150);
            $table->string('phone', 25);
            $table->text('address');
            $table->string('tax_number', 50)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
