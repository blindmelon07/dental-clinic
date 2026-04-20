<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'logo', 'clinic_name', 'tagline',
        'address', 'city', 'phone', 'email',
        'facebook_url', 'footer_text',
    ];

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'clinic_name' => 'DentCare Dental Clinic',
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
        return asset('images/gghi logo (1).png');
    }
}
