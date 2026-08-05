<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Clinic;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        for ($attempt = 1; ; $attempt++) {
            $data['invoice_number'] = Invoice::generateNumber();

            try {
                return parent::handleRecordCreation($data);
            } catch (QueryException $e) {
                $isDuplicateInvoiceNumber = (int) $e->getCode() === 23000
                    && str_contains($e->getMessage(), 'invoices_invoice_number_unique');

                if (! $isDuplicateInvoiceNumber || $attempt >= 5) {
                    throw $e;
                }
            }
        }
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();
    }
}
