<?php

namespace App\Observers;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentBookedMail;
use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentConfirmedMail;
use App\Models\Appointment;
use App\Models\AppointmentType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $appointment->loadMissing(['patient', 'dentist.user', 'service']);

        $email = $appointment->patient?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new AppointmentBookedMail($appointment));
        } catch (\Throwable $e) {
            Log::error('AppointmentBookedMail failed: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
            ]);
        }
    }

    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        // Update next cleaning due when a cleaning appointment is completed
        if ($appointment->status === AppointmentStatus::Completed) {
            $cleaningTypeNames = AppointmentType::where('is_cleaning', true)->pluck('name');
            $isCleaning = $cleaningTypeNames->contains($appointment->type)
                || str_contains(strtolower($appointment->service?->name ?? ''), 'cleaning');

            if ($isCleaning && $appointment->patient) {
                $appointment->patient->update([
                    'next_cleaning_due' => \Carbon\Carbon::parse($appointment->appointment_date)->addMonths(6),
                ]);
            }
        }

        $appointment->loadMissing(['patient', 'dentist.user', 'service']);

        $email = $appointment->patient?->email;
        if (! $email) {
            return;
        }

        try {
            if ($appointment->status === AppointmentStatus::Confirmed) {
                Mail::to($email)->send(new AppointmentConfirmedMail($appointment));
            } elseif ($appointment->status === AppointmentStatus::Cancelled) {
                Mail::to($email)->send(new AppointmentCancelledMail($appointment));
            }
        } catch (\Throwable $e) {
            Log::error('Appointment status email failed: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'status'         => $appointment->status->value,
            ]);
        }
    }
}
