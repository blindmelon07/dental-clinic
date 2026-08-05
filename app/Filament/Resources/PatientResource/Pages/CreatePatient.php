<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Clinic;
use App\Models\Patient;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CreatePatient extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PatientResource::class;

    public function getSteps(): array
    {
        return [
            Step::make('Patient Information')
                ->icon('heroicon-o-user')
                ->schema(PatientResource::patientInformationSchema()),
            Step::make('Dental & Medical History')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema(PatientResource::dentalMedicalHistorySchema()),
            Step::make('Informed Consent')
                ->icon('heroicon-o-document-check')
                ->schema(PatientResource::informedConsentSchema()),
        ];
    }

    public function getWizardComponent(): Component
    {
        $submitButtons = $this->getCreateFormAction()->toHtml()
            . ($this->canCreateAnother() ? $this->getCreateAnotherFormAction()->toHtml() : '');

        return Wizard::make($this->getSteps())
            ->startOnStep($this->getStartStep())
            ->cancelAction($this->getCancelFormAction())
            ->submitAction(new HtmlString($submitButtons))
            ->alpineSubmitHandler("\$wire.{$this->getSubmitFormLivewireMethodName()}()")
            ->contained(false)
            // "Create & create another" resets the form data but the wizard's Alpine
            // step state survives the Livewire re-render, so it stays parked on
            // whatever step was active. Jump back to the first step when that happens
            // (mirrors DentalRecordResource's tabs-reset fix for the same root cause).
            ->extraAlpineAttributes([
                'x-on:patient-wizard-reset.window' => 'step = JSON.parse($refs.stepsData.value)[0]',
            ]);
    }

    public function createAnother(): void
    {
        parent::createAnother();

        $this->dispatch('patient-wizard-reset');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        for ($attempt = 0; true; $attempt++) {
            $data['patient_number'] = $this->generatePatientNumber();

            try {
                return parent::handleRecordCreation($data);
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt >= 4 || ! str_contains($exception->getMessage(), 'patients_patient_number_unique')) {
                    throw $exception;
                }
            }
        }
    }

    protected function generatePatientNumber(): string
    {
        $prefix = 'PT-' . date('Ymd') . '-';
        $sequence = Patient::withTrashed()->whereDate('created_at', today())->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Patient::withTrashed()->where('patient_number', $number)->exists());

        return $number;
    }
}
