<?php

namespace App\Filament\Resources;

use App\Filament\Resources\XrayTypeResource\Pages;
use App\Models\XrayType;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class XrayTypeResource extends Resource
{
    protected static ?string $model = XrayType::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Radiographs';
    protected static ?string $modelLabel = 'Radiograph';
    protected static ?string $pluralModelLabel = 'Radiographs';

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_xray_type'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_xray_type'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_xray_type'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_xray_type'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_xray_type'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_xray_type'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('X-Ray Type Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),
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
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('sort_order')->sortable()->toggleable(),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListXrayTypes::route('/'),
            'create' => Pages\CreateXrayType::route('/create'),
            'edit'   => Pages\EditXrayType::route('/{record}/edit'),
        ];
    }
}
