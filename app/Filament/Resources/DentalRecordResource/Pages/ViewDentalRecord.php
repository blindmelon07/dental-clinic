<?php

namespace App\Filament\Resources\DentalRecordResource\Pages;

use App\Filament\Resources\DentalRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDentalRecord extends ViewRecord
{
    protected static string $resource = DentalRecordResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
