<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Clinic;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        return $data;
    }
}
