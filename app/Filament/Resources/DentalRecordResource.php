<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DentalRecordResource\Pages;
use App\Models\DentalRecord;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DentalRecordResource extends Resource
{
    protected static ?string $model = DentalRecord::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_dental_record'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_dental_record'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_dental_record'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_dental_record'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_dental_record'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_dental_record'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Visit Information')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required(),
                    Select::make('dentist_id')
                        ->label('Dentist')
                        ->relationship('dentist.user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('appointment_id')
                        ->label('Appointment')
                        ->relationship('appointment', 'appointment_number')
                        ->searchable()
                        ->nullable(),
                    DatePicker::make('visit_date')->required()->default(today()),
                ])->columns(2),

            Section::make('Clinical Assessment')
                ->schema([
                    Textarea::make('chief_complaint')->rows(3),
                    Textarea::make('diagnosis')->required()->rows(4),
                    Textarea::make('treatment_plan')->rows(4),
                    Textarea::make('treatment_done')->rows(4),
                ])->columns(2),

            Section::make('Prescription & Follow-up')
                ->schema([
                    Textarea::make('prescription')->rows(4),
                    Textarea::make('notes')->rows(4),
                    TextInput::make('next_visit_recommendation'),
                ])->columns(2),

            Section::make('X-Ray Images')
                ->schema([
                    Repeater::make('xrays')
                        ->relationship()
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('X-Ray Image')
                                ->image()
                                ->directory('xrays')
                                ->required(),
                            Select::make('xray_type')
                                ->options(['panoramic' => 'Panoramic', 'periapical' => 'Periapical', 'bitewing' => 'Bitewing', 'occlusal' => 'Occlusal'])
                                ->required(),
                            Textarea::make('findings')->rows(2),
                        ])->columns(3)->addActionLabel('Add X-Ray'),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Visit Information')
                ->schema([
                    TextEntry::make('visit_date')->date(),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('dentist.user.name')->label('Dentist')
                        ->formatStateUsing(fn ($state) => 'Dr. ' . $state),
                    TextEntry::make('appointment.appointment_number')->label('Appointment')->placeholder('—'),
                ])->columns(2),

            Section::make('Clinical Assessment')
                ->schema([
                    TextEntry::make('chief_complaint')->label('Chief Complaint')->placeholder('—'),
                    TextEntry::make('diagnosis'),
                    TextEntry::make('treatment_plan')->label('Treatment Plan')->placeholder('—'),
                    TextEntry::make('treatment_done')->label('Treatment Done')->placeholder('—'),
                ])->columns(2),

            Section::make('Prescription & Follow-up')
                ->schema([
                    TextEntry::make('prescription')->placeholder('—'),
                    TextEntry::make('notes')->placeholder('—'),
                    TextEntry::make('next_visit_recommendation')->label('Next Visit Recommendation')->placeholder('—'),
                ])->columns(2),

            Section::make('X-Ray Images')
                ->schema([
                    RepeatableEntry::make('xrays')
                        ->schema([
                            ImageEntry::make('file_path')->label('Image'),
                            TextEntry::make('xray_type')->label('Type')->badge(),
                            TextEntry::make('findings')->placeholder('—'),
                        ])->columns(3),
                ])
                ->visible(fn (DentalRecord $record) => $record->xrays->isNotEmpty()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('visit_date')->date()->sortable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                TextColumn::make('dentist.user.name')->label('Dentist')->searchable(),
                TextColumn::make('diagnosis')->limit(60)->searchable(),
                TextColumn::make('treatment_done')->limit(60),
                TextColumn::make('next_visit_recommendation')->limit(40),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('visit_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDentalRecords::route('/'),
            'create' => Pages\CreateDentalRecord::route('/create'),
            'view'   => Pages\ViewDentalRecord::route('/{record}'),
            'edit'   => Pages\EditDentalRecord::route('/{record}/edit'),
        ];
    }
}
