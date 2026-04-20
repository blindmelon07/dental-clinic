<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Dental Cleaning', 'Tooth Extraction', 'Root Canal', 'Dental Filling',
            'Teeth Whitening', 'Dental Crown', 'Dental Implant', 'Braces Consultation',
            'X-Ray (Panoramic)', 'X-Ray (Periapical)',
        ]);

        return [
            'clinic_id'           => Clinic::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'name'                => $name,
            'slug'                => Str::slug($name) . '-' . fake()->unique()->numerify('##'),
            'description'         => fake()->optional()->sentence(),
            'price'               => fake()->randomFloat(2, 200, 15000),
            'duration_minutes'    => fake()->randomElement([30, 45, 60, 90, 120]),
            'requires_xray'       => false,
            'is_active'           => true,
            'sort_order'          => fake()->numberBetween(1, 100),
        ];
    }
}
