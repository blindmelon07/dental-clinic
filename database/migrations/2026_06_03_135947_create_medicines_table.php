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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->enum('form', ['tablet', 'capsule', 'syrup', 'ointment', 'drops', 'injection', 'other'])->default('tablet');
            $table->string('strength')->nullable();
            $table->string('unit')->default('pcs');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
