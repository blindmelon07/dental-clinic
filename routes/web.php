<?php

use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\MyAppointments;
use App\Livewire\Patient\MyInvoices;
use App\Livewire\Patient\MyRecords;
use App\Models\Prescription;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->hasRole(['super_admin', 'admin', 'receptionist', 'dentist'])) {
            return redirect('/admin');
        }
        return redirect()->route('patient.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/appointments', MyAppointments::class)->name('appointments');
    Route::get('/book', BookAppointment::class)->name('book');
    Route::get('/records', MyRecords::class)->name('records');
    Route::get('/invoices', MyInvoices::class)->name('invoices');
});

// Prescription print — admin/staff only
Route::middleware(['auth'])->group(function () {
    Route::get('/prescriptions/{prescription}/print', function (Prescription $prescription) {
        abort_unless(
            auth()->user()->hasAnyRole(['super_admin', 'admin', 'dentist', 'receptionist']),
            403
        );

        $prescription->markPrinted();

        $clinic = $prescription->clinic ?? $prescription->patient->clinic;

        return view('prescriptions.print', compact('prescription', 'clinic'));
    })->name('prescription.print');
});

require __DIR__ . '/auth.php';
