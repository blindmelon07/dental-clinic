<?php

namespace App\Livewire\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BookAppointment extends Component
{
    public int $step = 1;
    public ?int $selectedServiceId = null;
    public ?int $selectedDentistId = null;
    public ?string $selectedDate = null;
    public ?string $selectedTime = null;
    public string $chiefComplaint = '';
    public string $appointmentType = 'Consultation';
    public bool $bookingComplete = false;
    public ?string $appointmentNumber = null;

    #[Computed]
    public function services(): \Illuminate\Database\Eloquent\Collection
    {
        return Service::where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function dentists(): \Illuminate\Database\Eloquent\Collection
    {
        return Dentist::where('is_active', true)
            ->with('user', 'schedules')
            ->get();
    }

    #[Computed]
    public function availableSlots(): array
    {
        if (! $this->selectedDentistId || ! $this->selectedDate) {
            return [];
        }

        $dentist = Dentist::find($this->selectedDentistId);
        if (! $dentist) return [];

        $dayOfWeek = (int) date('N', strtotime($this->selectedDate));
        $schedule = $dentist->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (! $schedule) return [];

        // Compare full time ranges, not just start times, so a longer appointment
        // also blocks the shorter slots it overlaps.
        $booked = Appointment::where('dentist_id', $this->selectedDentistId)
            ->whereDate('appointment_date', $this->selectedDate)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->get(['start_time', 'end_time'])
            ->map(fn ($a) => [strtotime($a->start_time), strtotime($a->end_time)]);

        $slots = [];
        $duration = $this->slotMinutes();

        $current = strtotime($schedule->start_time);
        $end = strtotime($schedule->end_time);

        while ($current + $duration * 60 <= $end) {
            $slotEnd = $current + $duration * 60;
            $overlaps = $booked->contains(fn ($b) => $current < $b[1] && $slotEnd > $b[0]);
            if (! $overlaps) {
                $slots[] = date('H:i', $current);
            }
            $current += $duration * 60;
        }

        return $slots;
    }

    /**
     * Length of one booking slot for the selected service. Services saved with a
     * 0 or empty duration fall back to 30 minutes; a 0-minute step would never
     * advance the slot loop above.
     */
    protected function slotMinutes(): int
    {
        return ($this->selectedServiceId
            ? Service::find($this->selectedServiceId)?->duration_minutes
            : null) ?: 30;
    }

    public function selectService(int $serviceId): void
    {
        $this->selectedServiceId = $serviceId;
        $this->step = 2;
    }

    public function selectDentist(int $dentistId): void
    {
        $this->selectedDentistId = $dentistId;
        $this->step = 3;
    }

    public function selectSlot(string $date, string $time): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = $time;
        $this->step = 4;
    }

    public function confirm(): void
    {
        if (! Auth::user()->is_active) {
            $this->addError('booking', 'Your account is pending approval by our staff before you can book appointments.');

            return;
        }

        $this->validate([
            'selectedServiceId'  => 'required|exists:services,id',
            'selectedDentistId'  => 'required|exists:dentists,id',
            // The date picker starts at tomorrow; keep the server rule in step with it.
            'selectedDate'       => 'required|date|after:today',
            'selectedTime'       => 'required',
        ]);

        $patient = Patient::where('user_id', Auth::id())->firstOrFail();
        $endTime = date('H:i', strtotime($this->selectedTime) + $this->slotMinutes() * 60);

        // Lock the dentist row so two patients confirming the same slot at once
        // are serialized, then re-check the slot against the current bookings.
        $appointment = DB::transaction(function () use ($patient, $endTime) {
            Dentist::whereKey($this->selectedDentistId)->lockForUpdate()->first();

            unset($this->availableSlots);
            if (! in_array($this->selectedTime, $this->availableSlots, true)) {
                return null;
            }

            return Appointment::create([
                'appointment_number' => Appointment::generateNumber(),
                'clinic_id'          => $patient->clinic_id,
                'patient_id'         => $patient->id,
                'dentist_id'         => $this->selectedDentistId,
                'service_id'         => $this->selectedServiceId,
                'appointment_date'   => $this->selectedDate,
                'start_time'         => $this->selectedTime,
                'end_time'           => $endTime,
                'type'               => $this->appointmentType,
                'status'             => AppointmentStatus::Pending,
                'chief_complaint'    => $this->chiefComplaint,
                'booked_by'          => Auth::id(),
            ]);
        });

        if (! $appointment) {
            $this->addError('booking', 'Sorry, that time slot is no longer available. Please choose another time.');
            $this->selectedTime = null;
            $this->step = 3;

            return;
        }

        $this->appointmentNumber = $appointment->appointment_number;
        $this->bookingComplete = true;
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    public function render()
    {
        return view('livewire.patient.book-appointment')
            ->layout('layouts.patient');
    }
}
