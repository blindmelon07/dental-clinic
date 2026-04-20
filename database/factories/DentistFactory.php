<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DentistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'clinic_id'             => Clinic::factory(),
            'license_number'        => 'LIC-' . fake()->numerify('######'),
            'specialization'        => fake()->randomElement(['General Dentistry', 'Orthodontics', 'Endodontics', 'Periodontics', 'Oral Surgery']),
            'bio'                   => fake()->paragraph(),
            'consultation_fee'      => fake()->randomFloat(2, 300, 2000),
            'consultation_duration' => fake()->randomElement([30, 45, 60]),
            'is_active'             => true,
        ];
    }
}
