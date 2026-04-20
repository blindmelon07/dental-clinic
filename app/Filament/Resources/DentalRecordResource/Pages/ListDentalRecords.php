<?php

namespace App\Filament\Resources\DentalRecordResource\Pages;

use App\Filament\Resources\DentalRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDentalRecords extends ListRecords
{
    protected static string $resource = DentalRecordResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
