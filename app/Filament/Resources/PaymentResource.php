<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Patient;
use App\Models\Payment;
use App\Enums\PaymentMethod;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null   $navigationGroup = 'Billing & Payments';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $navigationLabel = 'Payments';

    // Payments are recorded only through Invoice's "Record Payment" action — this
    // resource is a read-only ledger of what's already been collected.
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }

    public static function canViewAny(): bool { return auth()->user()?->can('view_any_invoice'); }
    public static function canView(Model $record): bool { return auth()->user()?->can('view_invoice'); }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment')
                ->schema([
                    TextEntry::make('payment_number')->label('Payment #')->copyable(),
                    TextEntry::make('invoice.invoice_number')->label('Invoice #')->placeholder('—'),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('amount')->money('PHP')->weight('bold'),
                    TextEntry::make('payment_method')->badge()->formatStateUsing(fn (PaymentMethod $state) => $state->label()),
                    TextEntry::make('reference_number')->label('Reference #')->placeholder('—'),
                    TextEntry::make('paid_at')->label('Paid At')->dateTime('M d, Y g:i A'),
                    TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')->label('Payment #')->searchable()->sortable()->copyable(),
                TextColumn::make('paid_at')->label('Paid At')->dateTime('M d, Y g:i A')->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['patient.first_name', 'patient.last_name']),
                TextColumn::make('invoice.invoice_number')->label('Invoice #')->searchable()->placeholder('—'),
                TextColumn::make('amount')->money('PHP')->sortable()->color('success')->weight('bold'),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (PaymentMethod $state) => $state->label()),
                TextColumn::make('reference_number')->label('Reference #')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')->options(PaymentMethod::class),

                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Patient $r) => $r->full_name),

                Filter::make('paid_at')
                    ->label('Paid Between')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '<=', $date));
                    }),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view'  => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
