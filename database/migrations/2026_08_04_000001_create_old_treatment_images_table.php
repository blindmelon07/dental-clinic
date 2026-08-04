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
        Schema::create('old_treatment_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_record_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_treatment_images');
    }
};
