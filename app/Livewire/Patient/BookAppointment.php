<?php

namespace App\Livewire\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
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
    public string $appointmentType = 'consultation';
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

        $bookedSlots = Appointment::where('dentist_id', $this->selectedDentistId)
            ->whereDate('appointment_date', $this->selectedDate)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->pluck('start_time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        $duration = $this->selectedServiceId
            ? Service::find($this->selectedServiceId)?->duration_minutes ?? 30
            : 30;

        $current = strtotime($schedule->start_time);
        $end = strtotime($schedule->end_time);

        while ($current + $duration * 60 <= $end) {
            $timeStr = date('H:i', $current);
            if (! in_array($timeStr, $bookedSlots)) {
                $slots[] = $timeStr;
            }
            $current += $duration * 60;
        }

        return $slots;
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
        $this->validate([
            'selectedServiceId'  => 'required|exists:services,id',
            'selectedDentistId'  => 'required|exists:dentists,id',
            'selectedDate'       => 'required|date|after_or_equal:today',
            'selectedTime'       => 'required',
        ]);

        $patient = Patient::where('user_id', Auth::id())->firstOrFail();
        $service = Service::findOrFail($this->selectedServiceId);
        $endTime = date('H:i', strtotime($this->selectedTime) + $service->duration_minutes * 60);

        $appointment = Appointment::create([
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
