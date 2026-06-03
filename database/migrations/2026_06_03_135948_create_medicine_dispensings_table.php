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
        Schema::create('medicine_dispensings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dispensed_by')->constrained('users')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamp('dispensed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_dispensings');
    }
};
