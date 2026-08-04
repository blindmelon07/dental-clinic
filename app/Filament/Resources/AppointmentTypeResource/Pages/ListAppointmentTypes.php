<?php

namespace App\Filament\Resources\AppointmentTypeResource\Pages;

use App\Filament\Resources\AppointmentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentTypes extends ListRecords
{
    protected static string $resource = AppointmentTypeResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
