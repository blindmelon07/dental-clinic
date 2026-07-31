<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use Auditable;

    protected $fillable = [
        'logo', 'clinic_name', 'tagline', 'site_theme',
        'hero_heading', 'hero_subheading', 'hero_description',
        'hero_image_1', 'hero_image_2', 'hero_image_3', 'hero_image_4',
        'stat_years', 'stat_patients', 'stat_satisfaction', 'stat_emergency',
        'address', 'city', 'phone', 'email',
        'hours_weekday', 'hours_saturday', 'hours_sunday',
        'facebook_url', 'footer_text', 'contact_location_image',
        'testimonial_quote', 'testimonial_author', 'testimonial_since',
        'home_about_image',
        'about_story_heading', 'about_story_body', 'about_story_image',
        'milestone_1_title', 'milestone_1_body',
        'milestone_2_title', 'milestone_2_body',
        'milestone_3_title', 'milestone_3_body',
        'milestone_4_title', 'milestone_4_body',
        'about_testimonial_quote', 'about_testimonial_author', 'about_testimonial_since',
    ];

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'clinic_name' => 'Gonzales Dental Clinic',
            'tagline'     => 'Your Trusted Dental Care Partner',
        ]);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::instance()->$key ?? $default;
    }

    public function logoUrl(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/logo.png');
    }
}
