<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipmentCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Diagnostic', 'Surgical', 'Sterilization', 'Imaging',
            'Restorative', 'Orthodontic', 'Furniture',
        ]);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numerify('##'),
            'description' => fake()->optional()->sentence(),
            'is_active'   => true,
        ];
    }
}
