<?php

namespace App\Livewire\Patient;

use App\Models\DentalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MyRecords extends Component
{
    use WithPagination;

    public ?int $selectedRecordId = null;

    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    #[Computed]
    public function records()
    {
        return DentalRecord::with(['dentist.user', 'xrays'])
            ->where('patient_id', $this->patient?->id)
            ->orderBy('visit_date', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function selectedRecord(): ?DentalRecord
    {
        if (! $this->selectedRecordId) return null;
        return DentalRecord::with(['dentist.user', 'xrays'])
            ->where('patient_id', $this->patient?->id)
            ->find($this->selectedRecordId);
    }

    public function viewRecord(int $id): void
    {
        $this->selectedRecordId = $id;
    }

    public function closeRecord(): void
    {
        $this->selectedRecordId = null;
    }

    public function render()
    {
        return view('livewire.patient.my-records')
            ->layout('layouts.patient');
    }
}
