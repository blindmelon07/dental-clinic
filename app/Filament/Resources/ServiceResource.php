<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_service'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_service'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_service'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_service'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_service'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_service'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Service Details')
                ->schema([
                    Select::make('service_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('name')->required()->maxLength(150)->live(onBlur: true),
                    Textarea::make('description')->rows(3),
                    TextInput::make('price')->numeric()->prefix('₱')->required(),
                    TextInput::make('duration_minutes')->numeric()->suffix('min')->default(30)->required(),
                    TextInput::make('sort_order')->numeric()->default(0),
                    Toggle::make('requires_xray')->default(false),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->label('Category')->badge()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('price')->money('PHP')->sortable(),
                TextColumn::make('duration_minutes')->suffix(' min')->sortable(),
                IconColumn::make('requires_xray')->boolean(),
                IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
