<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        $date = fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d');
        $startHour = fake()->numberBetween(8, 16);
        $startTime = sprintf('%02d:00:00', $startHour);
        $endTime   = sprintf('%02d:00:00', $startHour + 1);

        return [
            'appointment_number' => 'APT-' . date('Ymd') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'clinic_id'          => Clinic::factory(),
            'patient_id'         => Patient::factory(),
            'dentist_id'         => Dentist::factory(),
            'service_id'         => Service::factory(),
            'appointment_date'   => $date,
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'status'             => AppointmentStatus::Pending->value,
            'type'               => AppointmentType::Consultation->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status'       => AppointmentStatus::Confirmed->value,
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'              => AppointmentStatus::Cancelled->value,
            'cancellation_reason' => fake()->sentence(),
            'cancelled_at'        => now(),
        ]);
    }
}
