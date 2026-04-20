<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_patient'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_patient'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_patient'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_patient'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_patient'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_patient'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Information')
                ->schema([
                    TextInput::make('first_name')->required()->maxLength(100),
                    TextInput::make('middle_name')->maxLength(100),
                    TextInput::make('last_name')->required()->maxLength(100),
                    DatePicker::make('date_of_birth')->required()->maxDate(now()),
                    Select::make('gender')
                        ->options(Gender::class)
                        ->required(),
                    Select::make('blood_type')
                        ->options(['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'])
                        ->searchable(),
                ])->columns(3),

            Section::make('Contact Details')
                ->schema([
                    TextInput::make('phone')->required()->tel(),
                    TextInput::make('email')->email(),
                    Textarea::make('address')->required()->rows(2),
                    TextInput::make('city')->required(),
                ])->columns(2),

            Section::make('Emergency Contact')
                ->schema([
                    TextInput::make('emergency_contact_name'),
                    TextInput::make('emergency_contact_phone')->tel(),
                    TextInput::make('emergency_contact_relation'),
                ])->columns(3),

            Section::make('Medical History')
                ->schema([
                    Textarea::make('allergies')->rows(3),
                    Textarea::make('medical_conditions')->rows(3),
                    Textarea::make('current_medications')->rows(3),
                ])->columns(3),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Information')
                ->schema([
                    TextEntry::make('patient_number')->label('Patient #')->copyable(),
                    TextEntry::make('full_name')->label('Full Name'),
                    TextEntry::make('date_of_birth')->date()->label('Date of Birth'),
                    TextEntry::make('gender')->badge(),
                    TextEntry::make('blood_type')->label('Blood Type')->placeholder('—'),
                ])->columns(3),

            Section::make('Contact Details')
                ->schema([
                    TextEntry::make('phone'),
                    TextEntry::make('email')->placeholder('—'),
                    TextEntry::make('address')->placeholder('—'),
                    TextEntry::make('city')->placeholder('—'),
                ])->columns(2),

            Section::make('Emergency Contact')
                ->schema([
                    TextEntry::make('emergency_contact_name')->label('Name')->placeholder('—'),
                    TextEntry::make('emergency_contact_phone')->label('Phone')->placeholder('—'),
                    TextEntry::make('emergency_contact_relation')->label('Relation')->placeholder('—'),
                ])->columns(3),

            Section::make('Medical History')
                ->schema([
                    TextEntry::make('allergies')->placeholder('None recorded'),
                    TextEntry::make('medical_conditions')->label('Medical Conditions')->placeholder('None recorded'),
                    TextEntry::make('current_medications')->label('Current Medications')->placeholder('None recorded'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_number')->searchable()->sortable()->copyable(),
                TextColumn::make('full_name')->searchable(['first_name', 'last_name'])->sortable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('gender')->badge(),
                TextColumn::make('date_of_birth')->date()->sortable(),
                TextColumn::make('appointments_count')->counts('appointments')->sortable()->label('Visits'),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')->options(Gender::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PatientResource\RelationManagers\AppointmentsRelationManager::class,
            PatientResource\RelationManagers\DentalRecordsRelationManager::class,
            PatientResource\RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view'   => Pages\ViewPatient::route('/{record}'),
            'edit'   => Pages\EditPatient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('appointments');
    }
}
