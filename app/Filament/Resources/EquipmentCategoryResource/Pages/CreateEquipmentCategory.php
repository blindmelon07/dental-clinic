<?php

namespace App\Filament\Resources\EquipmentCategoryResource\Pages;

use App\Filament\Resources\EquipmentCategoryResource;
use App\Models\EquipmentCategory;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipmentCategory extends CreateRecord
{
    protected static string $resource = EquipmentCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = EquipmentCategory::uniqueSlug($data['name']);

        return $data;
    }
}
