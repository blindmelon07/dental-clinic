<?php

namespace App\Filament\Resources\XrayTypeResource\Pages;

use App\Filament\Resources\XrayTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListXrayTypes extends ListRecords
{
    protected static string $resource = XrayTypeResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
