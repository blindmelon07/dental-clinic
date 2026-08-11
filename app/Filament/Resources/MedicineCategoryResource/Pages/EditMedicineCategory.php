<?php

namespace App\Filament\Resources\MedicineCategoryResource\Pages;

use App\Filament\Resources\MedicineCategoryResource;
use App\Models\MedicineCategory;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicineCategory extends EditRecord
{
    protected static string $resource = MedicineCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = MedicineCategory::uniqueSlug($data['name'], $this->record->id);
        return $data;
    }
}
