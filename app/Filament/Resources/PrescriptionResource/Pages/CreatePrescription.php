<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use App\Models\Clinic;
use App\Models\Prescription;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prescription_number'] = Prescription::generateNumber();
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;

        return $data;
    }
}
