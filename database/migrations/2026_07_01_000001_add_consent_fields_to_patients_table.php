<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('consent_treatment_initials')->nullable();
            $table->string('consent_drugs_initials')->nullable();
            $table->string('consent_treatment_plan_initials')->nullable();
            $table->string('consent_radiograph_initials')->nullable();
            $table->string('consent_removal_of_teeth_initials')->nullable();
            $table->string('consent_crowns_initials')->nullable();
            $table->string('consent_endodontics_initials')->nullable();
            $table->string('consent_periodontal_initials')->nullable();
            $table->string('consent_fillings_initials')->nullable();
            $table->string('consent_dentures_initials')->nullable();
            $table->boolean('consent_agreed')->nullable()->default(false);
            $table->date('consent_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'consent_treatment_initials',
                'consent_drugs_initials',
                'consent_treatment_plan_initials',
                'consent_radiograph_initials',
                'consent_removal_of_teeth_initials',
                'consent_crowns_initials',
                'consent_endodontics_initials',
                'consent_periodontal_initials',
                'consent_fillings_initials',
                'consent_dentures_initials',
                'consent_agreed',
                'consent_date',
            ]);
        });
    }
};
