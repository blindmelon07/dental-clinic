<?php

namespace App\Filament\Resources\DentalRecordResource\Pages;

use App\Filament\Resources\DentalRecordResource;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditDentalRecord extends EditRecord
{
    protected static string $resource = DentalRecordResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Create a draft invoice using the services selected in this diagnosis.')
                ->visible(fn () => $this->record->invoices()->doesntExist() && filled($this->record->diagnosis))
                ->action(function () {
                    try {
                        $invoice = $this->record->createInvoice();
                    } catch (\RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                        return;
                    }

                    Notification::make()
                        ->title("Invoice {$invoice->invoice_number} generated.")
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                }),

            Action::make('viewInvoice')
                ->label('View Invoice')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn () => $this->record->invoices()->exists())
                ->url(fn () => InvoiceResource::getUrl('view', ['record' => $this->record->invoices()->latest()->first()])),

            DeleteAction::make(),
        ];
    }
}
