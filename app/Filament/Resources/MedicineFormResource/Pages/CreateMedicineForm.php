<?php

namespace App\Filament\Resources\MedicineFormResource\Pages;

use App\Filament\Resources\MedicineFormResource;
use App\Models\MedicineForm;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicineForm extends CreateRecord
{
    protected static string $resource = MedicineFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = MedicineForm::uniqueSlug($data['name']);
        return $data;
    }
}
