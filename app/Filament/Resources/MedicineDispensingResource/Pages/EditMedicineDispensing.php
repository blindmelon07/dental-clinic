<?php

namespace App\Filament\Resources\MedicineDispensingResource\Pages;

use App\Filament\Resources\MedicineDispensingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicineDispensing extends EditRecord
{
    protected static string $resource = MedicineDispensingResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
