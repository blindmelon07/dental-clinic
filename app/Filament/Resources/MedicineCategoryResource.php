<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineCategoryResource\Pages;
use App\Models\MedicineCategory;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicineCategoryResource extends Resource
{
    protected static ?string $model = MedicineCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Medicine Categories';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true),
                    ColorPicker::make('color')
                        ->default('#3B82F6'),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')->label('Color'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('medicines_count')
                    ->label('Medicines')
                    ->counts('medicines')
                    ->sortable(),
                TextColumn::make('description')->limit(50)->toggleable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('sort_order')->sortable()->toggleable(),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicineCategories::route('/'),
            'create' => Pages\CreateMedicineCategory::route('/create'),
            'edit'   => Pages\EditMedicineCategory::route('/{record}/edit'),
        ];
    }
}
