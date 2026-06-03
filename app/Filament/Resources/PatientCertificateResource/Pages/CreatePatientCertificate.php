<?php

namespace App\Filament\Resources\PatientCertificateResource\Pages;

use App\Filament\Resources\PatientCertificateResource;
use App\Models\PatientCertificate;
use App\Services\CertificateGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePatientCertificate extends CreateRecord
{
    protected static string $resource = PatientCertificateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['certificate_number'] = PatientCertificate::generateNumber();
        $data['issued_by']          = $data['issued_by'] ?? auth()->id();

        // Flatten checkbox list into individual boolean columns
        $treatments = $data['treatments'] ?? [];
        foreach (['cleaning', 'xray', 'anesthetic', 'extraction', 'root_canal', 'fillings'] as $key) {
            $data["treatment_{$key}"] = in_array("treatment_{$key}", $treatments);
        }
        unset($data['treatments']);

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            CertificateGenerator::generate($this->record);
            Notification::make()->title('Certificate generated and ready to download.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Saved, but generation failed: ' . $e->getMessage())->warning()->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
