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
        Schema::create('patient_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['certification', 'dental_clearance', 'medical_clearance']);
            $table->string('certificate_number')->unique();
            // Shared fields
            $table->date('date_treated');
            $table->date('issue_date');
            $table->text('findings')->nullable();
            $table->text('treatment_done')->nullable();
            $table->text('notes')->nullable();
            // Medical clearance specific
            $table->date('birthdate')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->boolean('treatment_cleaning')->default(false);
            $table->boolean('treatment_xray')->default(false);
            $table->boolean('treatment_anesthetic')->default(false);
            $table->boolean('treatment_extraction')->default(false);
            $table->boolean('treatment_root_canal')->default(false);
            $table->boolean('treatment_fillings')->default(false);
            $table->string('treatment_other')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_certificates');
    }
};
