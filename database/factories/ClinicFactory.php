<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company() . ' Dental Clinic';

        return [
            'name'      => $name,
            'slug'      => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numerify('####'),
            'email'     => fake()->unique()->safeEmail(),
            'phone'     => fake()->phoneNumber(),
            'address'   => fake()->streetAddress(),
            'city'      => fake()->city(),
            'is_active' => true,
        ];
    }
}
