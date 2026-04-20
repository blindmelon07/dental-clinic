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

    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    #[Computed]
    public function appointments()
    {
        return Appointment::with(['dentist.user', 'service'])
            ->where('patient_id', $this->patient?->id)
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);
    }

    public function cancelAppointment(int $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->patient_id !== $this->patient?->id) {
            abort(403);
        }

        if (! in_array($appointment->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed])) {
            $this->addError('cancel', 'This appointment cannot be cancelled.');
            return;
        }

        $appointment->cancel('Cancelled by patient');
        session()->flash('success', 'Appointment cancelled successfully.');
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
