<?php

namespace App\Filament\Resources\PatientCertificateResource\Pages;

use App\Filament\Resources\PatientCertificateResource;
use App\Services\CertificateGenerator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPatientCertificate extends EditRecord
{
    protected static string $resource = PatientCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }

    // Convert individual boolean columns → CheckboxList array when loading form
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $selected = [];
        foreach (['cleaning', 'xray', 'anesthetic', 'extraction', 'root_canal', 'fillings'] as $key) {
            if (! empty($data["treatment_{$key}"])) {
                $selected[] = "treatment_{$key}";
            }
        }
        $data['treatments'] = $selected;

        return $data;
    }

    // Convert CheckboxList array → individual boolean columns before saving
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $treatments = $data['treatments'] ?? [];
        foreach (['cleaning', 'xray', 'anesthetic', 'extraction', 'root_canal', 'fillings'] as $key) {
            $data["treatment_{$key}"] = in_array("treatment_{$key}", $treatments);
        }
        unset($data['treatments']);

        return $data;
    }

    protected function afterSave(): void
    {
        try {
            CertificateGenerator::generate($this->record);
            Notification::make()->title('Certificate regenerated.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Saved, but regeneration failed: ' . $e->getMessage())->warning()->send();
        }
    }
}
