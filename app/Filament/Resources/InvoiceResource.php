<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Billing & Payments';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_invoice'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_invoice'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_invoice'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_invoice'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_invoice'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_invoice'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Details')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required(),
                    Select::make('appointment_id')
                        ->label('Appointment')
                        ->options(function (Get $get) {
                            $patientId = $get('patient_id');
                            if (! $patientId) return [];
                            return \App\Models\Appointment::where('patient_id', $patientId)
                                ->orderByDesc('appointment_date')
                                ->get()
                                ->mapWithKeys(fn ($a) => [
                                    $a->id => "{$a->appointment_number} — {$a->appointment_date->format('M d, Y')}" . ($a->service ? " · {$a->service->name}" : ''),
                                ]);
                        })
                        ->searchable()
                        ->live()
                        ->nullable()
                        ->placeholder('Select patient first')
                        ->helperText('Filtered by selected patient'),
                    DatePicker::make('invoice_date')->required()->default(today()),
                    DatePicker::make('due_date'),
                    Select::make('status')
                        ->options(InvoiceStatus::class)
                        ->default(InvoiceStatus::Draft->value)
                        ->required(),
                    TextInput::make('tax_rate')->numeric()->suffix('%')->default(0),
                    TextInput::make('discount_amount')->numeric()->prefix('₱')->default(0),
                    Textarea::make('notes')->rows(2)->columnSpanFull(),
                ])
                ->columns(4)
                ->columnSpanFull(),

            Section::make('Line Items')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('service_id')
                                ->label('Service')
                                ->relationship('service', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->live()
                                ->columnSpan(4)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    if ($state && $service = \App\Models\Service::find($state)) {
                                        $set('description', $service->name);
                                        $set('unit_price', $service->price);
                                        $set('total', $service->price * ($get('quantity') ?? 1));
                                    }
                                }),
                            TextInput::make('description')
                                ->required()
                                ->columnSpan(4),
                            TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->live()
                                ->columnSpan(1)
                                ->afterStateUpdated(fn (Get $get, Set $set, $state) => $set('total', ($get('unit_price') ?? 0) * ($state ?? 1))),
                            TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->prefix('₱')
                                ->live()
                                ->columnSpan(2)
                                ->afterStateUpdated(fn (Get $get, Set $set, $state) => $set('total', $state * ($get('quantity') ?? 1))),
                            TextInput::make('total')
                                ->numeric()
                                ->prefix('₱')
                                ->readOnly()
                                ->columnSpan(2),
                        ])
                        ->columns(13)
                        ->addActionLabel('+ Add Item')
                        ->reorderableWithButtons()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? null),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Details')
                ->schema([
                    TextEntry::make('invoice_number')->label('Invoice #')->copyable(),
                    TextEntry::make('status')->badge()
                        ->color(fn (InvoiceStatus $state): string => $state->color()),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('appointment.appointment_number')->label('Appointment')->placeholder('—'),
                    TextEntry::make('invoice_date')->date(),
                    TextEntry::make('due_date')->date()->placeholder('—'),
                    TextEntry::make('tax_rate')->suffix('%'),
                    TextEntry::make('discount_amount')->money('PHP'),
                    TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                ])->columns(2),

            Section::make('Line Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('description'),
                            TextEntry::make('quantity'),
                            TextEntry::make('unit_price')->money('PHP'),
                            TextEntry::make('total')->money('PHP'),
                        ])->columns(4),
                ]),

            Section::make('Totals')
                ->schema([
                    TextEntry::make('subtotal')->money('PHP'),
                    TextEntry::make('discount_amount')->label('Discount')->money('PHP'),
                    TextEntry::make('tax_amount')->label('Tax')->money('PHP'),
                    TextEntry::make('total')->money('PHP')->weight('bold'),
                    TextEntry::make('amount_paid')->money('PHP')
                        ->color('success'),
                    TextEntry::make('balance_due')->money('PHP')
                        ->color(fn (Invoice $record) => $record->balance_due > 0 ? 'danger' : 'success'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->searchable()->sortable()->copyable(),
                TextColumn::make('patient.full_name')->searchable()->label('Patient'),
                TextColumn::make('invoice_date')->date()->sortable(),
                TextColumn::make('total')->money('PHP')->sortable(),
                TextColumn::make('amount_paid')->money('PHP'),
                TextColumn::make('balance_due')->money('PHP')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (InvoiceStatus $state): string => $state->color()),
            ])
            ->filters([SelectFilter::make('status')->options(InvoiceStatus::class)])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('invoice_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view'   => Pages\ViewInvoice::route('/{record}'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
