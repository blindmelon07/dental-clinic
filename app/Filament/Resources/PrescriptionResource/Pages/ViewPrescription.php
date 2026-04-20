<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrescription extends ViewRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print Prescription')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('prescription.print', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
