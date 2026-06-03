<?php

namespace App\Filament\Resources\MedicineDispensingResource\Pages;

use App\Filament\Resources\MedicineDispensingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMedicineDispensing extends ViewRecord
{
    protected static string $resource = MedicineDispensingResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
