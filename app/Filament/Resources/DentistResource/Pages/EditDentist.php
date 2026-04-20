<?php

namespace App\Filament\Resources\DentistResource\Pages;

use App\Filament\Resources\DentistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDentist extends EditRecord
{
    protected static string $resource = DentistResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
