<?php

namespace App\Filament\Resources;

use App\Enums\EquipmentStatus;
use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Equipment';
    protected static ?string $modelLabel = 'Equipment';
    protected static ?string $pluralModelLabel = 'Equipment';

    public static function canViewAny(): bool { return auth()->user()?->can('view_any_equipment'); }
    public static function canView(Model $record): bool { return auth()->user()?->can('view_equipment'); }
    public static function canCreate(): bool { return auth()->user()?->can('create_equipment'); }
    public static function canEdit(Model $record): bool { return auth()->user()?->can('update_equipment'); }
    public static function canDelete(Model $record): bool { return auth()->user()?->can('delete_equipment'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_equipment'); }

    public static function getNavigationBadge(): ?string
    {
        $dueOrDown = Equipment::where('next_maintenance_date', '<=', now())
            ->orWhereIn('status', [EquipmentStatus::UnderMaintenance, EquipmentStatus::OutOfService])
            ->count();

        return $dueOrDown > 0 ? (string) $dueOrDown : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Equipment Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('equipment_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->options(EquipmentStatus::class)
                        ->default(EquipmentStatus::Operational->value)
                        ->required(),
                    TextInput::make('serial_number')
                        ->label('Serial Number')
                        ->maxLength(150)
                        ->unique(ignoreRecord: true),
                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                    TextInput::make('location')
                        ->placeholder('e.g. Operatory 2')
                        ->maxLength(150),
                ])->columns(3),

            Section::make('Purchase & Warranty')
                ->schema([
                    TextInput::make('supplier')->maxLength(150),
                    TextInput::make('purchase_cost')
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0),
                    DatePicker::make('purchase_date'),
                    DatePicker::make('warranty_expires_at')->label('Warranty Expires'),
                ])->columns(2),

            Section::make('Maintenance')
                ->schema([
                    DatePicker::make('last_maintenance_date')->label('Last Maintenance'),
                    DatePicker::make('next_maintenance_date')->label('Next Maintenance Due'),
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Equipment Details')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('category.name')->label('Category')->placeholder('—'),
                    TextEntry::make('serial_number')->label('Serial Number')->placeholder('—'),
                    TextEntry::make('status')->badge()
                        ->color(fn (EquipmentStatus $state): string => $state->color())
                        ->formatStateUsing(fn (EquipmentStatus $state): string => $state->label()),
                    TextEntry::make('quantity'),
                    TextEntry::make('location')->placeholder('—'),
                ])->columns(3),

            Section::make('Purchase & Warranty')
                ->schema([
                    TextEntry::make('supplier')->placeholder('—'),
                    TextEntry::make('purchase_cost')->label('Purchase Cost')->money('PHP')->placeholder('—'),
                    TextEntry::make('purchase_date')->date()->placeholder('—'),
                    TextEntry::make('warranty_expires_at')->label('Warranty Expires')->date()->placeholder('No warranty on record')
                        ->color(fn (Equipment $record): ?string => $record->isUnderWarranty() ? 'success' : null),
                ])->columns(2),

            Section::make('Maintenance')
                ->schema([
                    TextEntry::make('last_maintenance_date')->label('Last Maintenance')->date()->placeholder('—'),
                    TextEntry::make('next_maintenance_date')->label('Next Maintenance Due')->date()->placeholder('—')
                        ->color(fn (Equipment $record): ?string =>
                            $record->isMaintenanceDue() ? 'danger' : ($record->isMaintenanceDueSoon() ? 'warning' : null)
                        ),
                    TextEntry::make('notes')->placeholder('None')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category')->placeholder('—')->sortable(),
                TextColumn::make('serial_number')->label('Serial No.')->placeholder('—')->searchable()->toggleable(),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (EquipmentStatus $state): string => $state->color())
                    ->formatStateUsing(fn (EquipmentStatus $state): string => $state->label()),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('location')->placeholder('—')->toggleable(),
                TextColumn::make('next_maintenance_date')
                    ->label('Next Maintenance')
                    ->date('M d, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (Equipment $record): ?string =>
                        $record->isMaintenanceDue() ? 'danger' : ($record->isMaintenanceDueSoon() ? 'warning' : null)
                    ),
            ])
            ->filters([
                SelectFilter::make('status')->options(EquipmentStatus::class),
                SelectFilter::make('equipment_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                TernaryFilter::make('maintenance_due')
                    ->label('Maintenance Due')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '<=', now()),
                        false: fn (Builder $q) => $q->where(function (Builder $q) {
                            $q->whereNull('next_maintenance_date')->orWhere('next_maintenance_date', '>', now());
                        }),
                    ),
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
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'view' => Pages\ViewEquipment::route('/{record}'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
