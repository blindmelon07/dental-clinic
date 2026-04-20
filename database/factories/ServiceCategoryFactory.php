<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Preventive Care', 'Restorative', 'Cosmetic', 'Orthodontics',
            'Oral Surgery', 'Endodontics', 'Periodontics', 'Pediatric',
        ]);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numerify('##'),
            'description' => fake()->optional()->sentence(),
            'is_active'   => true,
        ];
    }
}
