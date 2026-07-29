<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $dentist = Dentist::first();
        $patients = Patient::all();

        if (! $dentist || $patients->isEmpty()) {
            $this->command->warn('No dentist or patients found — run DatabaseSeeder first.');
            return;
        }

        $weekStart = today()->startOfWeek();

        $appointments = [
            ['day' => 0, 'time' => '09:00', 'patient' => 0, 'service' => 'general-dentistry-dental-consultation', 'type' => AppointmentType::Consultation, 'status' => AppointmentStatus::Confirmed],
            ['day' => 0, 'time' => '09:00', 'patient' => 1, 'service' => 'general-dentistry-teeth-cleaning-prophylaxis', 'type' => AppointmentType::Cleaning, 'status' => AppointmentStatus::Pending],
            ['day' => 1, 'time' => '10:00', 'patient' => 2, 'service' => 'cosmetic-dentistry-dental-bonding', 'type' => AppointmentType::Procedure, 'status' => AppointmentStatus::Confirmed],
            ['day' => 2, 'time' => '11:00', 'patient' => 5, 'service' => 'panoramic-standard', 'type' => AppointmentType::XRay, 'status' => AppointmentStatus::Pending],
            ['day' => 2, 'time' => '14:00', 'patient' => 6, 'service' => 'restorative-dentistry-root-canal-treatment', 'type' => AppointmentType::Procedure, 'status' => AppointmentStatus::InProgress],
            ['day' => 3, 'time' => '15:00', 'patient' => 3, 'service' => 'general-dentistry-tooth-extraction-surgical', 'type' => AppointmentType::Procedure, 'status' => AppointmentStatus::Confirmed],
            ['day' => 4, 'time' => '09:00', 'patient' => 0, 'service' => 'general-dentistry-dental-consultation', 'type' => AppointmentType::FollowUp, 'status' => AppointmentStatus::Cancelled],
        ];

        $created = 0;

        foreach ($appointments as $data) {
            $service = Service::where('slug', $data['service'])->first();

            if (! $service) {
                continue;
            }

            $patient = $patients->get($data['patient']) ?? $patients->first();
            $start = \Carbon\Carbon::parse($data['time']);
            $end = $start->copy()->addMinutes($service->duration_minutes ?: 30);

            Appointment::create([
                'appointment_number' => Appointment::generateNumber(),
                'clinic_id'          => $dentist->clinic_id,
                'patient_id'         => $patient->id,
                'dentist_id'         => $dentist->id,
                'service_id'         => $service->id,
                'appointment_date'   => $weekStart->copy()->addDays($data['day']),
                'start_time'         => $start->format('H:i'),
                'end_time'           => $end->format('H:i'),
                'status'             => $data['status'],
                'type'               => $data['type'],
            ]);

            $created++;
        }

        $this->command->info('✅ Appointments seeded for the current week!');
        $this->command->table(['What was seeded', 'Count'], [['Appointments', $created]]);
    }
}
