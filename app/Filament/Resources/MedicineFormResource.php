<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineFormResource\Pages;
use App\Models\MedicineForm;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicineFormResource extends Resource
{
    protected static ?string $model = MedicineForm::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Medicine Forms';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Form Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->toggleable(),
                TextColumn::make('medicines_count')
                    ->label('Medicines')
                    ->counts('medicines')
                    ->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicineForms::route('/'),
            'create' => Pages\CreateMedicineForm::route('/create'),
            'edit'   => Pages\EditMedicineForm::route('/{record}/edit'),
        ];
    }
}
