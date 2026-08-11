<?php

namespace App\Filament\Resources\MedicineCategoryResource\Pages;

use App\Filament\Resources\MedicineCategoryResource;
use App\Models\MedicineCategory;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicineCategory extends CreateRecord
{
    protected static string $resource = MedicineCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = MedicineCategory::uniqueSlug($data['name']);
        return $data;
    }
}
