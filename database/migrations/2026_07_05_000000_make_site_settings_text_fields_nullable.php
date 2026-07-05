<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'hero_heading'         => ['type' => 'VARCHAR(255)', 'default' => 'Healthy Smiles'],
        'hero_subheading'      => ['type' => 'VARCHAR(255)', 'default' => 'Start Here'],
        'stat_years'           => ['type' => 'VARCHAR(255)', 'default' => '15+'],
        'stat_patients'        => ['type' => 'VARCHAR(255)', 'default' => '10k+'],
        'stat_satisfaction'    => ['type' => 'VARCHAR(255)', 'default' => '98%'],
        'stat_emergency'       => ['type' => 'VARCHAR(255)', 'default' => '24/7'],
        'hours_weekday'        => ['type' => 'VARCHAR(255)', 'default' => '9:00 AM – 6:00 PM'],
        'hours_saturday'       => ['type' => 'VARCHAR(255)', 'default' => '9:00 AM – 2:00 PM'],
        'hours_sunday'         => ['type' => 'VARCHAR(255)', 'default' => 'Closed'],
        'about_story_heading'  => ['type' => 'VARCHAR(255)', 'default' => 'Care With Compassion'],
        'milestone_1_title'    => ['type' => 'VARCHAR(255)', 'default' => 'Our Clinic Opens'],
        'milestone_2_title'    => ['type' => 'VARCHAR(255)', 'default' => 'Expanding Our Services'],
        'milestone_3_title'    => ['type' => 'VARCHAR(255)', 'default' => 'Going Digital'],
        'milestone_4_title'    => ['type' => 'VARCHAR(255)', 'default' => 'Serving You Today'],
    ];

    /**
     * These columns are optional in the Site Settings form and every page
     * template already falls back with `?:` when they're empty, but the
     * columns were created NOT NULL. Saving the form with one of them
     * cleared caused a 1048 "cannot be null" SQL error.
     */
    public function up(): void
    {
        foreach ($this->columns as $name => $def) {
            DB::statement("ALTER TABLE site_settings MODIFY `{$name}` {$def['type']} NULL DEFAULT " . DB::getPdo()->quote($def['default']));
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $name => $def) {
            DB::statement("ALTER TABLE site_settings MODIFY `{$name}` {$def['type']} NOT NULL DEFAULT " . DB::getPdo()->quote($def['default']));
        }
    }
};
