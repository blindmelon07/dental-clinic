<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-beaker';
    protected static string|\UnitEnum|null   $navigationGroup = 'Inventory';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationLabel = 'Medicines';

    public static function getNavigationBadge(): ?string
    {
        $low = Medicine::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        return $low > 0 ? (string) $low : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Medicine Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Medicine Name'),

                    TextInput::make('generic_name')
                        ->maxLength(255)
                        ->label('Generic Name'),

                    TextInput::make('brand')
                        ->maxLength(255)
                        ->label('Brand / Manufacturer'),

                    Select::make('category')
                        ->options([
                            'antibiotic'       => 'Antibiotic',
                            'analgesic'        => 'Analgesic / Pain Reliever',
                            'anti_inflammatory'=> 'Anti-Inflammatory',
                            'anesthetic'       => 'Anesthetic',
                            'antiseptic'       => 'Antiseptic',
                            'antifungal'       => 'Antifungal',
                            'antihistamine'    => 'Antihistamine',
                            'vitamin'          => 'Vitamin / Supplement',
                            'other'            => 'Other',
                        ])
                        ->searchable(),

                    Select::make('form')
                        ->options([
                            'tablet'    => 'Tablet',
                            'capsule'   => 'Capsule',
                            'syrup'     => 'Syrup / Liquid',
                            'ointment'  => 'Ointment / Cream',
                            'drops'     => 'Drops',
                            'injection' => 'Injection',
                            'other'     => 'Other',
                        ])
                        ->required()
                        ->default('tablet'),

                    TextInput::make('strength')
                        ->placeholder('e.g. 500mg, 250mg/5ml')
                        ->maxLength(100),

                    TextInput::make('unit')
                        ->placeholder('e.g. pcs, bottles, boxes')
                        ->default('pcs')
                        ->required(),

                    TextInput::make('unit_price')
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0)
                        ->default(0),
                ])->columns(3),

            Section::make('Stock Information')
                ->schema([
                    TextInput::make('current_stock')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(0)
                        ->label('Current Stock'),

                    TextInput::make('minimum_stock')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(10)
                        ->label('Minimum Stock (Alert Threshold)'),

                    DatePicker::make('expiry_date')
                        ->label('Expiry Date')
                        ->nullable(),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Active'),
                ])->columns(2),

            Section::make('Additional Information')
                ->schema([
                    Textarea::make('description')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Medicine Details')
                ->schema([
                    TextEntry::make('name')->label('Medicine Name'),
                    TextEntry::make('generic_name')->placeholder('—'),
                    TextEntry::make('brand')->placeholder('—'),
                    TextEntry::make('category')
                        ->formatStateUsing(fn (?string $state) => $state ? ucwords(str_replace('_', ' ', $state)) : '—'),
                    TextEntry::make('form')->badge()->formatStateUsing(fn ($state) => ucfirst($state)),
                    TextEntry::make('strength')->placeholder('—'),
                ])->columns(3),

            Section::make('Stock')
                ->schema([
                    TextEntry::make('current_stock')
                        ->label('Current Stock')
                        ->badge()
                        ->color(fn (Medicine $record): string => $record->isLowStock() ? 'danger' : 'success')
                        ->formatStateUsing(fn (Medicine $record): string => $record->current_stock . ' ' . $record->unit),

                    TextEntry::make('minimum_stock')
                        ->label('Minimum Stock')
                        ->formatStateUsing(fn (Medicine $record): string => $record->minimum_stock . ' ' . $record->unit),

                    TextEntry::make('unit_price')->label('Unit Price')->prefix('₱'),

                    TextEntry::make('expiry_date')->date()->placeholder('No expiry set')
                        ->color(fn (Medicine $record): ?string =>
                            $record->isExpired() ? 'danger' : ($record->isExpiringSoon() ? 'warning' : null)
                        ),
                ])->columns(2),

            Section::make('Description')
                ->schema([
                    TextEntry::make('description')->placeholder('None')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Medicine $r): string => implode(' · ', array_filter([$r->generic_name, $r->brand]))),

                TextColumn::make('category')
                    ->formatStateUsing(fn (?string $state) => $state ? ucwords(str_replace('_', ' ', $state)) : '—')
                    ->sortable(),

                TextColumn::make('form')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('strength')->placeholder('—'),

                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->badge()
                    ->sortable()
                    ->color(fn (Medicine $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->formatStateUsing(fn (Medicine $r): string => $r->current_stock . ' ' . $r->unit),

                TextColumn::make('minimum_stock')
                    ->label('Min. Stock')
                    ->sortable()
                    ->formatStateUsing(fn (Medicine $r): string => $r->minimum_stock . ' ' . $r->unit),

                TextColumn::make('expiry_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (Medicine $r): ?string =>
                        $r->isExpired() ? 'danger' : ($r->isExpiringSoon() ? 'warning' : null)
                    ),

                TextColumn::make('unit_price')->label('Unit Price')->money('PHP')->sortable(),

                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'antibiotic'       => 'Antibiotic',
                        'analgesic'        => 'Analgesic / Pain Reliever',
                        'anti_inflammatory'=> 'Anti-Inflammatory',
                        'anesthetic'       => 'Anesthetic',
                        'antiseptic'       => 'Antiseptic',
                        'antifungal'       => 'Antifungal',
                        'antihistamine'    => 'Antihistamine',
                        'vitamin'          => 'Vitamin / Supplement',
                        'other'            => 'Other',
                    ]),

                SelectFilter::make('form')
                    ->options([
                        'tablet'   => 'Tablet', 'capsule'  => 'Capsule',
                        'syrup'    => 'Syrup',  'ointment' => 'Ointment',
                        'drops'    => 'Drops',  'injection'=> 'Injection',
                        'other'    => 'Other',
                    ]),

                TernaryFilter::make('low_stock')
                    ->label('Low Stock Only')
                    ->queries(
                        true:  fn (Builder $q) => $q->whereColumn('current_stock', '<=', 'minimum_stock'),
                        false: fn (Builder $q) => $q->whereColumn('current_stock', '>', 'minimum_stock'),
                    ),

                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'view'   => Pages\ViewMedicine::route('/{record}'),
            'edit'   => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
