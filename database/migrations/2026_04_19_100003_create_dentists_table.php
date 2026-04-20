<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('license_number')->unique();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->integer('consultation_duration')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dentist_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentist_schedules');
        Schema::dropIfExists('dentists');
    }
};
