<?php

namespace App\Filament\Resources\XrayTypeResource\Pages;

use App\Filament\Resources\XrayTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditXrayType extends EditRecord
{
    protected static string $resource = XrayTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
