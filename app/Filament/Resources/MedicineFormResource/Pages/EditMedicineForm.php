<?php

namespace App\Filament\Resources\MedicineFormResource\Pages;

use App\Filament\Resources\MedicineFormResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditMedicineForm extends EditRecord
{
    protected static string $resource = MedicineFormResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        return $data;
    }
}
