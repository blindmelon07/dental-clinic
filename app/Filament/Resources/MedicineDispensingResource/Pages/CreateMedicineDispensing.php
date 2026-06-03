<?php

namespace App\Filament\Resources\MedicineDispensingResource\Pages;

use App\Filament\Resources\MedicineDispensingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicineDispensing extends CreateRecord
{
    protected static string $resource = MedicineDispensingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
