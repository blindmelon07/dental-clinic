<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Clinic;
use App\Models\Patient;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        for ($attempt = 0; true; $attempt++) {
            $data['patient_number'] = $this->generatePatientNumber();

            try {
                return parent::handleRecordCreation($data);
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt >= 4 || ! str_contains($exception->getMessage(), 'patients_patient_number_unique')) {
                    throw $exception;
                }
            }
        }
    }

    protected function generatePatientNumber(): string
    {
        $prefix = 'PT-' . date('Ymd') . '-';
        $sequence = Patient::withTrashed()->whereDate('created_at', today())->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Patient::withTrashed()->where('patient_number', $number)->exists());

        return $number;
    }
}
