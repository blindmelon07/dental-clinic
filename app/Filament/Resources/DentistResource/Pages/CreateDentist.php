<?php

namespace App\Filament\Resources\DentistResource\Pages;

use App\Filament\Resources\DentistResource;
use App\Models\Clinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDentist extends CreateRecord
{
    protected static string $resource = DentistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        return $data;
    }
}
