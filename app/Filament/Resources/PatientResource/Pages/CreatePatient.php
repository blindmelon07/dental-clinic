<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Clinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['user_id'] = Auth::id();
        $data['patient_number'] = 'PT-' . date('Ymd') . '-' . str_pad(
            (\App\Models\Patient::whereDate('created_at', today())->count() + 1),
            4, '0', STR_PAD_LEFT
        );
        return $data;
    }
}
