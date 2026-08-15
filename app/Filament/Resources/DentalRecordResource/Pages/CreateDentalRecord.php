<?php

namespace App\Filament\Resources\DentalRecordResource\Pages;

use App\Filament\Resources\DentalRecordResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDentalRecord extends CreateRecord
{
    protected static string $resource = DentalRecordResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DentalRecordResource::diagnosisRowsToData($data);
    }

    public function createAnother(): void
    {
        parent::createAnother();

        $this->dispatch('dental-record-tabs-reset');
    }
}
