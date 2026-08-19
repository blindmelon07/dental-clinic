<?php

namespace App\Filament\Resources\EquipmentCategoryResource\Pages;

use App\Filament\Resources\EquipmentCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentCategories extends ListRecords
{
    protected static string $resource = EquipmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
