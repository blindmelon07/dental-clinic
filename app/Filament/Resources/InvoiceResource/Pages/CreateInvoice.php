<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Clinic;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = Auth::user()->clinic_id ?? Clinic::first()?->id;
        $data['invoice_number'] = Invoice::generateNumber();
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();
    }
}
