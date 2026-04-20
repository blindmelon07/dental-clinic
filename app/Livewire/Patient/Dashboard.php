<?php

namespace App\Livewire\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    #[Computed]
    public function upcomingAppointments()
    {
        return Appointment::with(['dentist.user', 'service'])
            ->where('patient_id', $this->patient?->id)
            ->whereDate('appointment_date', '>=', today())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentRecords()
    {
        return \App\Models\DentalRecord::with('dentist.user')
            ->where('patient_id', $this->patient?->id)
            ->orderBy('visit_date', 'desc')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function unpaidInvoices()
    {
        return Invoice::where('patient_id', $this->patient?->id)
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.patient.dashboard')
            ->layout('layouts.patient');
    }
}
