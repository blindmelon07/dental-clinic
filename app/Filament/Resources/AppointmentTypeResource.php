<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentTypeResource\Pages;
use App\Models\AppointmentType;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentTypeResource extends Resource
{
    protected static ?string $model = AppointmentType::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Appointment Types';
    protected static ?string $modelLabel = 'Appointment Type';
    protected static ?string $pluralModelLabel = 'Appointment Types';

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_appointment_type'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_appointment_type'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_appointment_type'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_appointment_type'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_appointment_type'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_appointment_type'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Appointment Type Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_cleaning')
                        ->label('Counts as a cleaning')
                        ->helperText('Completed appointments of this type update the patient\'s next cleaning due date and trigger cleaning reminder emails.')
                        ->default(false),
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
                IconColumn::make('is_cleaning')->boolean()->sortable()->label('Cleaning'),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('sort_order')->sortable()->toggleable(),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointmentTypes::route('/'),
            'create' => Pages\CreateAppointmentType::route('/create'),
            'edit'   => Pages\EditAppointmentType::route('/{record}/edit'),
        ];
    }
}
