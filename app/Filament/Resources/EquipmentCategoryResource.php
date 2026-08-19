<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentCategoryResource\Pages;
use App\Models\EquipmentCategory;
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
use Illuminate\Database\Eloquent\Model;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Equipment Categories';

    public static function canViewAny(): bool { return auth()->user()?->can('view_any_equipment_category'); }
    public static function canView(Model $record): bool { return auth()->user()?->can('view_equipment_category'); }
    public static function canCreate(): bool { return auth()->user()?->can('create_equipment_category'); }
    public static function canEdit(Model $record): bool { return auth()->user()?->can('update_equipment_category'); }
    public static function canDelete(Model $record): bool { return auth()->user()?->can('delete_equipment_category'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_equipment_category'); }

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
                TextColumn::make('equipment_count')
                    ->label('Equipment')
                    ->counts('equipment')
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
            'index'  => Pages\ListEquipmentCategories::route('/'),
            'create' => Pages\CreateEquipmentCategory::route('/create'),
            'edit'   => Pages\EditEquipmentCategory::route('/{record}/edit'),
        ];
    }
}
