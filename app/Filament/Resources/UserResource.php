<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_user'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_user'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_user'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_user'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_user'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_user'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    TextInput::make('name')->required()->maxLength(100),
                    TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->label('Password'),
                    Select::make('roles')
                        ->multiple()
                        ->preload()
                        ->relationship('roles', 'name')
                        ->label('Roles'),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),

            Section::make('Personal Information')
                ->schema([
                    TextInput::make('phone')->tel(),
                    DatePicker::make('date_of_birth'),
                    Select::make('gender')->options(Gender::class),
                    TextInput::make('city'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('phone'),
                TextColumn::make('roles.name')->badge()->searchable()->label('Roles'),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name'),
            ])
            ->recordActions([EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
