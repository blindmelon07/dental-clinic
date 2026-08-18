<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToothConditionResource\Pages;
use App\Models\ToothCondition;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ToothConditionResource extends Resource
{
    protected static ?string $model = ToothCondition::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?string $navigationLabel = 'Tooth Chart Conditions';
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_tooth_condition'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_tooth_condition'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_tooth_condition'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_tooth_condition'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_tooth_condition'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_tooth_condition'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Condition Details')
                ->schema([
                    Select::make('group')
                        ->options(array_combine(ToothCondition::GROUPS, ToothCondition::GROUPS))
                        ->required(),
                    TextInput::make('code')
                        ->label('Code (shown on the tooth)')
                        ->required()
                        ->maxLength(10)
                        ->unique(ignoreRecord: true),
                    TextInput::make('label')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                    ColorPicker::make('color')
                        ->required()
                        ->default('#3b82f6'),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first within the group.'),
                    Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Inactive conditions are hidden from the tooth chart palette.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')->badge()->sortable(),
                TextColumn::make('code')->weight('bold')->sortable(),
                TextColumn::make('label')->searchable()->sortable(),
                ColorColumn::make('color'),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')->options(array_combine(ToothCondition::GROUPS, ToothCondition::GROUPS)),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('group')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListToothConditions::route('/'),
            'create' => Pages\CreateToothCondition::route('/create'),
            'edit'   => Pages\EditToothCondition::route('/{record}/edit'),
        ];
    }
}
