<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Mail\CleaningReminderDentistMail;
use App\Mail\CleaningReminderPatientMail;
use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCleaningReminders extends Command
{
    protected $signature   = 'reminders:send-cleaning';
    protected $description = 'Send 5-day advance email reminders for upcoming dental cleanings';

    public function handle(): int
    {
        $targetDate = now()->addDays(5)->toDateString();

        $patients = Patient::with(['user', 'appointments.dentist.user', 'appointments.service'])
            ->where('is_active', true)
            ->whereNotNull('next_cleaning_due')
            ->whereDate('next_cleaning_due', $targetDate)
            ->get();

        if ($patients->isEmpty()) {
            $this->info("No cleaning reminders to send for {$targetDate}.");
            return self::SUCCESS;
        }

        $sent   = 0;
        $failed = 0;

        foreach ($patients as $patient) {
            // Patient email
            $patientEmail = $patient->user?->email ?? $patient->email;

            if ($patientEmail) {
                try {
                    Mail::to($patientEmail)->send(new CleaningReminderPatientMail($patient));
                    $this->info("  ✔ Patient email → {$patient->full_name} <{$patientEmail}>");
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error("  ✘ Patient email failed → {$patient->full_name}: {$e->getMessage()}");
                    $failed++;
                }
            }

            // Dentist email — use the dentist from the last completed cleaning
            $lastCleaning = $patient->appointments()
                ->with('dentist.user')
                ->where('status', AppointmentStatus::Completed)
                ->where(function ($q) {
                    $q->where('type', AppointmentType::Cleaning)
                      ->orWhereHas('service', fn ($s) =>
                          $s->whereRaw('LOWER(name) LIKE ?', ['%cleaning%'])
                      );
                })
                ->latest('appointment_date')
                ->first();

            $dentist = $lastCleaning?->dentist;

            if ($dentist?->user?->email) {
                try {
                    Mail::to($dentist->user->email)->send(
                        new CleaningReminderDentistMail($patient, $dentist)
                    );
                    $this->info("  ✔ Dentist email → Dr. {$dentist->user->name} <{$dentist->user->email}>");
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error("  ✘ Dentist email failed → Dr. {$dentist->user->name}: {$e->getMessage()}");
                    $failed++;
                }
            }
        }

        $this->info("Done. {$sent} email(s) sent, {$failed} failed. ({$patients->count()} patient(s) processed)");

        return self::SUCCESS;
    }
}
