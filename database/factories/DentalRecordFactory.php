<?php

namespace Database\Factories;

use App\Models\Dentist;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class DentalRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id'               => Patient::factory(),
            'dentist_id'               => Dentist::factory(),
            'appointment_id'           => null,
            'visit_date'               => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'chief_complaint'          => fake()->sentence(),
            'diagnosis'                => fake()->sentences(2, true),
            'treatment_plan'           => fake()->optional()->sentences(2, true),
            'treatment_done'           => fake()->optional()->sentences(2, true),
            'prescription'             => fake()->optional()->sentences(2, true),
            'notes'                    => fake()->optional()->sentence(),
            'next_visit_recommendation' => fake()->optional()->randomElement(['1 week', '2 weeks', '1 month', '3 months', '6 months']),
        ];
    }
}
