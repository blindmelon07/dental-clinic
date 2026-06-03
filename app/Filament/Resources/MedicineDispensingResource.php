<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineDispensingResource\Pages;
use App\Models\Medicine;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\Prescription;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MedicineDispensingResource extends Resource
{
    protected static ?string $model = MedicineDispensing::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static string|\UnitEnum|null   $navigationGroup = 'Inventory';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $navigationLabel = 'Dispensing Records';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dispensing Details')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $r) => $r->full_name . ' (' . $r->patient_number . ')')
                        ->searchable(['first_name', 'last_name', 'patient_number'])
                        ->preload()
                        ->required()
                        ->live(),

                    Select::make('prescription_id')
                        ->label('Linked Prescription (optional)')
                        ->options(fn (Get $get) => Prescription::where('patient_id', $get('patient_id'))
                            ->get()
                            ->mapWithKeys(fn ($p) => [$p->id => $p->prescription_number . ' — ' . $p->prescribed_date->format('M d, Y')])
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('medicine_id')
                        ->label('Medicine')
                        ->options(fn () => Medicine::where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($m) => [
                                $m->id => $m->display_name . ' (Stock: ' . $m->current_stock . ' ' . $m->unit . ')'
                            ])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if ($state && $medicine = Medicine::find($state)) {
                                $set('unit_price', $medicine->unit_price);
                            }
                        }),

                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),

                    TextInput::make('unit_price')
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0)
                        ->default(0)
                        ->label('Unit Price (₱)'),

                    Select::make('dispensed_by')
                        ->label('Dispensed By')
                        ->relationship('dispensedBy', 'name')
                        ->default(fn () => auth()->id())
                        ->required(),

                    DateTimePicker::make('dispensed_at')
                        ->label('Dispensed At')
                        ->default(now())
                        ->required(),

                    Textarea::make('notes')->rows(2)->nullable()->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dispensing Record')
                ->schema([
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('medicine.name')->label('Medicine'),
                    TextEntry::make('medicine.strength')->label('Strength')->placeholder('—'),
                    TextEntry::make('prescription.prescription_number')->label('Prescription #')->placeholder('—'),
                    TextEntry::make('quantity'),
                    TextEntry::make('unit_price')->prefix('₱'),
                    TextEntry::make('dispensedBy.name')->label('Dispensed By'),
                    TextEntry::make('dispensed_at')->dateTime(),
                ])->columns(3),

            Section::make('Notes')
                ->schema([
                    TextEntry::make('notes')->placeholder('None')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dispensed_at')
                    ->label('Date')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),

                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['patient.first_name', 'patient.last_name']),

                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->description(fn (MedicineDispensing $r): string => $r->medicine->strength ?? '')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->suffix(fn (MedicineDispensing $r): string => ' ' . ($r->medicine->unit ?? 'pcs')),

                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('PHP'),

                TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('PHP')
                    ->state(fn (MedicineDispensing $r): float => $r->quantity * $r->unit_price),

                TextColumn::make('prescription.prescription_number')
                    ->label('Prescription #')
                    ->placeholder('—'),

                TextColumn::make('dispensedBy.name')->label('Dispensed By'),
            ])
            ->filters([
                SelectFilter::make('medicine_id')
                    ->label('Medicine')
                    ->relationship('medicine', 'name'),

                SelectFilter::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Patient $r) => $r->full_name),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('dispensed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicineDispensings::route('/'),
            'create' => Pages\CreateMedicineDispensing::route('/create'),
            'view'   => Pages\ViewMedicineDispensing::route('/{record}'),
            'edit'   => Pages\EditMedicineDispensing::route('/{record}/edit'),
        ];
    }
}
