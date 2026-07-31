<?php

namespace App\Filament\Resources\MedicineFormResource\Pages;

use App\Filament\Resources\MedicineFormResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMedicineForm extends CreateRecord
{
    protected static string $resource = MedicineFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        return $data;
    }
}
