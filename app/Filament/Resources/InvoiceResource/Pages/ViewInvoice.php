<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\InvoiceResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recordPayment')
                ->label('Record Payment')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [
                    InvoiceStatus::Sent,
                    InvoiceStatus::PartiallyPaid,
                    InvoiceStatus::Overdue,
                    InvoiceStatus::Draft,
                ]))
                ->form([
                    TextInput::make('amount')
                        ->label('Payment Amount (₱)')
                        ->numeric()
                        ->prefix('₱')
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn () => (float) $this->record->balance_due)
                        ->default(fn () => (float) $this->record->balance_due)
                        ->helperText(fn () => 'Balance due: ₱' . number_format($this->record->balance_due, 2)),

                    Select::make('payment_method')
                        ->label('Payment Method')
                        ->options(PaymentMethod::class)
                        ->default(PaymentMethod::Cash->value)
                        ->required(),

                    TextInput::make('reference_number')
                        ->label('Reference / Receipt No. (optional)')
                        ->maxLength(100)
                        ->placeholder('e.g. GCash ref, bank transaction ID'),

                    DateTimePicker::make('paid_at')
                        ->label('Payment Date & Time')
                        ->default(now())
                        ->required(),

                    Textarea::make('notes')
                        ->rows(2)
                        ->placeholder('Optional notes about this payment'),
                ])
                ->action(function (array $data) {
                    $invoice = $this->record;

                    Payment::create([
                        'payment_number'   => Payment::generateNumber(),
                        'invoice_id'       => $invoice->id,
                        'patient_id'       => $invoice->patient_id,
                        'amount'           => $data['amount'],
                        'payment_method'   => $data['payment_method'],
                        'reference_number' => $data['reference_number'] ?? null,
                        'notes'            => $data['notes'] ?? null,
                        'paid_at'          => $data['paid_at'],
                    ]);

                    $invoice->recalculate();

                    // Auto-update status
                    if ($invoice->fresh()->balance_due <= 0) {
                        $invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
                    } elseif ($invoice->fresh()->amount_paid > 0) {
                        $invoice->update(['status' => InvoiceStatus::PartiallyPaid]);
                    }

                    Notification::make()
                        ->title('Payment of ₱' . number_format($data['amount'], 2) . ' recorded successfully.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'amount_paid', 'balance_due']);
                }),

            EditAction::make(),
        ];
    }
}
