<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use App\Models\Prescription;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prescription_number'] = Prescription::generateNumber();
        $data['clinic_id'] = auth()->user()->clinic_id ?? 1;

        return $data;
    }
}
