<?php

namespace App\Filament\Resources\MedicineFormResource\Pages;

use App\Filament\Resources\MedicineFormResource;
use App\Models\MedicineForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicineForm extends EditRecord
{
    protected static string $resource = MedicineFormResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = MedicineForm::uniqueSlug($data['name'], $this->record->id);
        return $data;
    }
}
