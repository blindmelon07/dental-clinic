<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dentist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis');
            $table->text('treatment_plan')->nullable();
            $table->text('treatment_done')->nullable();
            $table->json('tooth_chart')->nullable();
            $table->text('prescription')->nullable();
            $table->text('notes')->nullable();
            $table->string('next_visit_recommendation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dental_xrays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_record_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('xray_type')->nullable();
            $table->text('findings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_xrays');
        Schema::dropIfExists('dental_records');
    }
};
