<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicineFormFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Tablet', 'Capsule', 'Syrup / Liquid', 'Ointment / Cream', 'Drops', 'Injection',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numerify('##'),
        ];
    }
}
