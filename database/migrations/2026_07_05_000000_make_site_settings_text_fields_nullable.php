<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These columns are optional in the Site Settings form and every page
     * template already falls back with `?:` when they're empty, but the
     * columns were created NOT NULL. Saving the form with one of them
     * cleared caused a 1048 "cannot be null" SQL error.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('hero_heading')->nullable()->default('Healthy Smiles')->change();
            $table->string('hero_subheading')->nullable()->default('Start Here')->change();
            $table->string('stat_years')->nullable()->default('15+')->change();
            $table->string('stat_patients')->nullable()->default('10k+')->change();
            $table->string('stat_satisfaction')->nullable()->default('98%')->change();
            $table->string('stat_emergency')->nullable()->default('24/7')->change();
            $table->string('hours_weekday')->nullable()->default('9:00 AM – 6:00 PM')->change();
            $table->string('hours_saturday')->nullable()->default('9:00 AM – 2:00 PM')->change();
            $table->string('hours_sunday')->nullable()->default('Closed')->change();
            $table->string('about_story_heading')->nullable()->default('Care With Compassion')->change();
            $table->string('milestone_1_title')->nullable()->default('Our Clinic Opens')->change();
            $table->string('milestone_2_title')->nullable()->default('Expanding Our Services')->change();
            $table->string('milestone_3_title')->nullable()->default('Going Digital')->change();
            $table->string('milestone_4_title')->nullable()->default('Serving You Today')->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('hero_heading')->default('Healthy Smiles')->change();
            $table->string('hero_subheading')->default('Start Here')->change();
            $table->string('stat_years')->default('15+')->change();
            $table->string('stat_patients')->default('10k+')->change();
            $table->string('stat_satisfaction')->default('98%')->change();
            $table->string('stat_emergency')->default('24/7')->change();
            $table->string('hours_weekday')->default('9:00 AM – 6:00 PM')->change();
            $table->string('hours_saturday')->default('9:00 AM – 2:00 PM')->change();
            $table->string('hours_sunday')->default('Closed')->change();
            $table->string('about_story_heading')->default('Care With Compassion')->change();
            $table->string('milestone_1_title')->default('Our Clinic Opens')->change();
            $table->string('milestone_2_title')->default('Expanding Our Services')->change();
            $table->string('milestone_3_title')->default('Going Digital')->change();
            $table->string('milestone_4_title')->default('Serving You Today')->change();
        });
    }
};
