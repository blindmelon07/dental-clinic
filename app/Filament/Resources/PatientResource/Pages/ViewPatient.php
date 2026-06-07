<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_referral')
                ->label('Print Referral Form')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('patient.referral.print', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
