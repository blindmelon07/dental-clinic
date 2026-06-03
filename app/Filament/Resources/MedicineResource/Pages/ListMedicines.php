<?php

namespace App\Filament\Resources\MedicineResource\Pages;

use App\Filament\Resources\MedicineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
