<?php

use App\Models\Patient;
use App\Models\PatientCertificate;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\MyAppointments;
use App\Livewire\Patient\MyInvoices;
use App\Livewire\Patient\MyRecords;
use App\Livewire\Patient\Profile;
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
    Route::get('/profile', Profile::class)->name('profile');
});

// Certificate routes — staff only
Route::middleware(['auth'])->group(function () {
    Route::get('/certificates/{certificate}/download', function (PatientCertificate $certificate) {
        abort_unless(
            auth()->user()->hasAnyRole(['super_admin', 'admin', 'dentist', 'receptionist']),
            403
        );
        $path = storage_path('app/public/' . $certificate->file_path);
        abort_unless(file_exists($path), 404);
        $filename = $certificate->typeLabel() . ' - ' . $certificate->patient->full_name . ' (' . $certificate->certificate_number . ').docx';
        return response()->download($path, $filename);
    })->name('certificate.download');

    Route::get('/certificates/{certificate}/print', function (PatientCertificate $certificate) {
        abort_unless(
            auth()->user()->hasAnyRole(['super_admin', 'admin', 'dentist', 'receptionist']),
            403
        );
        $clinic  = \App\Models\SiteSetting::instance();
        $dentist = $certificate->issuedBy;
        return view('certificates.print', compact('certificate', 'clinic', 'dentist'));
    })->name('certificate.print');
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

// Referral form print — staff only
Route::middleware(['auth'])->group(function () {
    Route::get('/patients/{patient}/referral/print', function (Patient $patient) {
        abort_unless(
            auth()->user()->hasAnyRole(['super_admin', 'admin', 'dentist', 'receptionist']),
            403
        );
        $clinic = \App\Models\SiteSetting::instance();
        return view('referrals.print', compact('patient', 'clinic'));
    })->name('patient.referral.print');
});

require __DIR__ . '/auth.php';
