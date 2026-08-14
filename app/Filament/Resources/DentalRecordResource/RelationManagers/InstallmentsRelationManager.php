<?php

namespace App\Filament\Resources\DentalRecordResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\DentalRecord;
use App\Models\Payment;
use App\Models\PaymentPlanInstallment;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';
    protected static ?string $title = 'Payment Plan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var DentalRecord $ownerRecord */
        return $ownerRecord->paymentPlan()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('installment_number')
            ->columns([
                TextColumn::make('installment_number')->label('#')->sortable(),
                TextColumn::make('due_date')->date('M d, Y')->sortable(),
                TextColumn::make('amount')->money('PHP')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (PaymentPlanInstallment $record) => $record->statusLabel())
                    ->color(fn (PaymentPlanInstallment $record) => $record->statusColor()),
                TextColumn::make('payment.payment_number')->label('Payment #')->placeholder('—'),
                TextColumn::make('paid_at')->label('Paid At')->dateTime('M d, Y g:i A')->placeholder('—'),
            ])
            ->recordActions([
                Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (PaymentPlanInstallment $record) => ! $record->isPaid())
                    ->form([
                        TextInput::make('amount')
                            ->label('Payment Amount (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->minValue(0.01)
                            ->default(fn (PaymentPlanInstallment $record) => (float) $record->amount),

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
                    ->action(function (PaymentPlanInstallment $record, array $data) {
                        /** @var DentalRecord $dentalRecord */
                        $dentalRecord = $this->getOwnerRecord();
                        $invoice = $dentalRecord->getOrCreateInvoice();

                        $payment = Payment::create([
                            'payment_number'   => Payment::generateNumber(),
                            'invoice_id'       => $invoice->id,
                            'patient_id'       => $dentalRecord->patient_id,
                            'amount'           => $data['amount'],
                            'payment_method'   => $data['payment_method'],
                            'reference_number' => $data['reference_number'] ?? null,
                            'notes'            => $data['notes'] ?? "Installment #{$record->installment_number} payment.",
                            'paid_at'          => $data['paid_at'],
                        ]);

                        $invoice->recalculate();

                        if ($invoice->fresh()->balance_due <= 0) {
                            $invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
                        } elseif ($invoice->fresh()->amount_paid > 0) {
                            $invoice->update(['status' => InvoiceStatus::PartiallyPaid]);
                        }

                        $record->update([
                            'payment_id' => $payment->id,
                            'paid_at'    => $data['paid_at'],
                        ]);

                        Notification::make()
                            ->title('Installment #' . $record->installment_number . ' — payment of ₱' . number_format($data['amount'], 2) . ' recorded.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('installment_number');
    }
}
