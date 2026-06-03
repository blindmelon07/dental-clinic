<?php

namespace App\Filament\Resources\PatientCertificateResource\Pages;

use App\Filament\Resources\PatientCertificateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatientCertificates extends ListRecords
{
    protected static string $resource = PatientCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Issue Certificate / Clearance')];
    }
}
