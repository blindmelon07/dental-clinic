<?php

namespace App\Observers;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;

class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->status !== AppointmentStatus::Completed) {
            return;
        }

        $isCleaning = $appointment->type === AppointmentType::Cleaning
            || str_contains(strtolower($appointment->service?->name ?? ''), 'cleaning');

        if ($isCleaning && $appointment->patient) {
            $appointment->patient->update([
                'next_cleaning_due' => $appointment->appointment_date->addMonths(6),
            ]);
        }
    }
}
