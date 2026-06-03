<?php

namespace App\Filament\Resources\MedicineDispensingResource\Pages;

use App\Filament\Resources\MedicineDispensingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicineDispensings extends ListRecords
{
    protected static string $resource = MedicineDispensingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Record Dispensing')];
    }
}
