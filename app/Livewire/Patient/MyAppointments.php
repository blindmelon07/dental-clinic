<?php

namespace App\Livewire\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MyAppointments extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';

    public bool $showCancelModal = false;
    public ?int $cancellingId = null;
    public string $cancelAppointmentNumber = '';
    public string $cancelServiceName = '';
    public string $cancelDate = '';
    public string $cancelDentist = '';
    public string $cancelReason = '';

    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    #[Computed]
    public function appointments()
    {
        return Appointment::with(['dentist.user', 'service.category'])
            ->where('patient_id', $this->patient?->id)
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);
    }

    public function openCancelModal(int $id): void
    {
        $appointment = Appointment::with(['service', 'dentist.user'])->findOrFail($id);

        if ($appointment->patient_id !== $this->patient?->id) {
            abort(403);
        }

        $this->cancellingId            = $id;
        $this->cancelAppointmentNumber = $appointment->appointment_number;
        $this->cancelServiceName       = $appointment->service->name ?? 'N/A';
        $this->cancelDate              = $appointment->appointment_date->format('F d, Y') . ' at ' . date('g:i A', strtotime($appointment->start_time));
        $dentistName = $appointment->dentist->user->name ?? 'N/A';
        $this->cancelDentist = str_starts_with($dentistName, 'Dr.') ? $dentistName : 'Dr. ' . $dentistName;
        $this->showCancelModal         = true;
    }

    public function confirmCancel(): void
    {
        if (! $this->cancellingId) {
            return;
        }

        $appointment = Appointment::findOrFail($this->cancellingId);

        if ($appointment->patient_id !== $this->patient?->id) {
            abort(403);
        }

        if (! in_array($appointment->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed])) {
            $this->closeCancelModal();
            $this->addError('cancel', 'This appointment cannot be cancelled.');
            return;
        }

        $reason = 'Cancelled by patient' . ($this->cancelReason ? ': ' . $this->cancelReason : '');
        $appointment->cancel($reason);
        $this->closeCancelModal();
        session()->flash('success', 'Your appointment has been cancelled successfully.');
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal         = false;
        $this->cancellingId            = null;
        $this->cancelAppointmentNumber = '';
        $this->cancelServiceName       = '';
        $this->cancelDate              = '';
        $this->cancelDentist           = '';
        $this->cancelReason            = '';
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.patient.my-appointments')
            ->layout('layouts.patient');
    }
}
