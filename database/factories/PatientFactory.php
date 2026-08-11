<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Filament\Resources\PatientResource;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'user_id'        => User::factory(),
            'clinic_id'      => Clinic::factory(),
            'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'first_name'     => fake()->firstName(),
            'last_name'      => fake()->lastName(),
            'middle_name'    => fake()->optional(0.4)->firstName(),
            'date_of_birth'  => fake()->dateTimeBetween('-70 years', '-5 years')->format('Y-m-d'),
            'gender'         => fake()->randomElement(Gender::cases())->value,
            'blood_type'     => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'phone'          => fake()->phoneNumber(),
            'email'          => fake()->optional(0.7)->safeEmail(),
            'address'        => fake()->streetAddress(),
            // Must come from the resource's closed dropdown list, not an arbitrary
            // fake city — the Edit form's city Select rejects any value outside it,
            // which otherwise silently blocks re-saving a factory-made patient.
            'city'           => fake()->randomElement(array_keys(PatientResource::cityOptions())),
            'is_active'      => true,
            'consent_agreed' => true,
        ];
    }
}
