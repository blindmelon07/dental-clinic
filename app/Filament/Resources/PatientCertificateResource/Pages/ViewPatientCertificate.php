<?php

namespace App\Filament\Resources\PatientCertificateResource\Pages;

use App\Filament\Resources\PatientCertificateResource;
use App\Services\CertificateGenerator;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPatientCertificate extends ViewRecord
{
    protected static string $resource = PatientCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('certificate.print', $this->record))
                ->openUrlInNewTab(),

            Action::make('download')
                ->label('Download .docx')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => (bool) $this->record->file_path)
                ->url(fn () => route('certificate.download', $this->record))
                ->openUrlInNewTab(),

            Action::make('regenerate')
                ->label('Regenerate')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    try {
                        CertificateGenerator::generate($this->record);
                        Notification::make()->title('Document regenerated.')->success()->send();
                        $this->refreshFormData(['file_path', 'generated_at']);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),

            EditAction::make(),
        ];
    }
}
