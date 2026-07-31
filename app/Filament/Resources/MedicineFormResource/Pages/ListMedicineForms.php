<?php

namespace App\Filament\Resources\MedicineFormResource\Pages;

use App\Filament\Resources\MedicineFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicineForms extends ListRecords
{
    protected static string $resource = MedicineFormResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
