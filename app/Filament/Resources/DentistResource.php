<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DentistResource\Pages;
use App\Models\Dentist;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DentistResource extends Resource
{
    protected static ?string $model = Dentist::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_dentist'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_dentist'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_dentist'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_dentist'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_dentist'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_dentist'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dentist Profile')
                ->schema([
                    Select::make('user_id')
                        ->label('Staff User')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('license_number')->required()->unique(ignoreRecord: true),
                    TextInput::make('specialization'),
                    TextInput::make('consultation_fee')->numeric()->prefix('₱')->required(),
                    TextInput::make('consultation_duration')->numeric()->suffix('min')->default(30)->required(),
                    Textarea::make('bio')->rows(4),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),

            Section::make('Weekly Schedule')
                ->schema([
                    Repeater::make('schedules')
                        ->relationship()
                        ->schema([
                            Select::make('day_of_week')
                                ->options([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'])
                                ->required(),
                            TimePicker::make('start_time')->required()->seconds(false),
                            TimePicker::make('end_time')->required()->seconds(false),
                            Toggle::make('is_available')->default(true),
                        ])->columns(4)->addActionLabel('Add Schedule'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('license_number')->searchable(),
                TextColumn::make('specialization')->searchable(),
                TextColumn::make('consultation_fee')->money('PHP')->sortable(),
                TextColumn::make('consultation_duration')->suffix(' min'),
                TextColumn::make('appointments_count')->counts('appointments')->label('Total Appts')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDentists::route('/'),
            'create' => Pages\CreateDentist::route('/create'),
            'edit'   => Pages\EditDentist::route('/{record}/edit'),
        ];
    }
}
