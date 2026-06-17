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
        Schema::table('site_settings', function (Blueprint $table) {
            // Hero section
            $table->string('hero_heading')->default('Healthy Smiles')->after('tagline');
            $table->string('hero_subheading')->default('Start Here')->after('hero_heading');
            $table->text('hero_description')->nullable()->after('hero_subheading');

            // Stats
            $table->string('stat_years')->default('15+')->after('hero_description');
            $table->string('stat_patients')->default('10k+')->after('stat_years');
            $table->string('stat_satisfaction')->default('98%')->after('stat_patients');
            $table->string('stat_emergency')->default('24/7')->after('stat_satisfaction');

            // Clinic hours
            $table->string('hours_weekday')->default('9:00 AM – 6:00 PM')->after('stat_emergency');
            $table->string('hours_saturday')->default('9:00 AM – 2:00 PM')->after('hours_weekday');
            $table->string('hours_sunday')->default('Closed')->after('hours_saturday');

            // Home testimonial
            $table->text('testimonial_quote')->nullable()->after('hours_sunday');
            $table->string('testimonial_author')->nullable()->after('testimonial_quote');
            $table->string('testimonial_since')->nullable()->after('testimonial_author');

            // About page – story
            $table->string('about_story_heading')->default('Care With Compassion')->after('testimonial_since');
            $table->text('about_story_body')->nullable()->after('about_story_heading');

            // About page – milestones
            $table->string('milestone_1_title')->default('Our Clinic Opens')->after('about_story_body');
            $table->text('milestone_1_body')->nullable()->after('milestone_1_title');
            $table->string('milestone_2_title')->default('Expanding Our Services')->after('milestone_1_body');
            $table->text('milestone_2_body')->nullable()->after('milestone_2_title');
            $table->string('milestone_3_title')->default('Going Digital')->after('milestone_2_body');
            $table->text('milestone_3_body')->nullable()->after('milestone_3_title');
            $table->string('milestone_4_title')->default('Serving You Today')->after('milestone_3_body');
            $table->text('milestone_4_body')->nullable()->after('milestone_4_title');

            // About testimonial
            $table->text('about_testimonial_quote')->nullable()->after('milestone_4_body');
            $table->string('about_testimonial_author')->nullable()->after('about_testimonial_quote');
            $table->string('about_testimonial_since')->nullable()->after('about_testimonial_author');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_heading', 'hero_subheading', 'hero_description',
                'stat_years', 'stat_patients', 'stat_satisfaction', 'stat_emergency',
                'hours_weekday', 'hours_saturday', 'hours_sunday',
                'testimonial_quote', 'testimonial_author', 'testimonial_since',
                'about_story_heading', 'about_story_body',
                'milestone_1_title', 'milestone_1_body',
                'milestone_2_title', 'milestone_2_body',
                'milestone_3_title', 'milestone_3_body',
                'milestone_4_title', 'milestone_4_body',
                'about_testimonial_quote', 'about_testimonial_author', 'about_testimonial_since',
            ]);
        });
    }
};
