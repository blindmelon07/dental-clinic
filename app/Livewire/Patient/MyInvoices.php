<?php

namespace App\Livewire\Patient;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MyInvoices extends Component
{
    use WithPagination;

    #[Computed]
    public function patient(): ?Patient
    {
        return Patient::where('user_id', Auth::id())->first();
    }

    #[Computed]
    public function invoices()
    {
        return Invoice::with(['items.service', 'payments'])
            ->where('patient_id', $this->patient?->id)
            ->orderBy('invoice_date', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.patient.my-invoices')
            ->layout('layouts.patient');
    }
}
