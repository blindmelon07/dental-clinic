<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicineCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Antibiotic', 'Analgesic', 'Anti-Inflammatory', 'Anesthetic',
            'Antiseptic', 'Antifungal', 'Antihistamine', 'Vitamin',
        ]);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numerify('##'),
            'description' => fake()->optional()->sentence(),
            'is_active'   => true,
        ];
    }
}
