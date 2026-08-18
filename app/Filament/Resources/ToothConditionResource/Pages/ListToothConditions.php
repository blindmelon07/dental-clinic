<?php

namespace App\Filament\Resources\ToothConditionResource\Pages;

use App\Filament\Resources\ToothConditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListToothConditions extends ListRecords
{
    protected static string $resource = ToothConditionResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
