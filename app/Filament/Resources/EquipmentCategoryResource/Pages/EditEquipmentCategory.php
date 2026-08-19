<?php

namespace App\Filament\Resources\EquipmentCategoryResource\Pages;

use App\Filament\Resources\EquipmentCategoryResource;
use App\Models\EquipmentCategory;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentCategory extends EditRecord
{
    protected static string $resource = EquipmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = EquipmentCategory::uniqueSlug($data['name'], $this->record->id);

        return $data;
    }
}
