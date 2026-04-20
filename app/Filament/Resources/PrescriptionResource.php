<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_prescription'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_prescription'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_prescription'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_prescription'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_prescription'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Prescription Info')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required(),
                    Select::make('dentist_id')
                        ->label('Prescribing Dentist')
                        ->relationship('dentist', 'id')
                        ->getOptionLabelFromRecordUsing(fn (Dentist $record) => 'Dr. ' . $record->user->name)
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('prescribed_date')
                        ->required()
                        ->default(today()),
                    Select::make('appointment_id')
                        ->label('Appointment (optional)')
                        ->relationship('appointment', 'appointment_number')
                        ->searchable()
                        ->nullable(),
                    Textarea::make('diagnosis')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Medications')
                ->schema([
                    Repeater::make('medications')
                        ->schema([
                            TextInput::make('name')
                                ->label('Drug / Medicine Name')
                                ->required()
                                ->placeholder('e.g. Amoxicillin'),
                            TextInput::make('strength')
                                ->label('Strength')
                                ->placeholder('e.g. 500mg'),
                            Select::make('form')
                                ->label('Form')
                                ->options([
                                    'tablet'   => 'Tablet',
                                    'capsule'  => 'Capsule',
                                    'syrup'    => 'Syrup',
                                    'ointment' => 'Ointment',
                                    'drops'    => 'Drops',
                                    'injection'=> 'Injection',
                                    'other'    => 'Other',
                                ]),
                            TextInput::make('dose')
                                ->label('Dose')
                                ->placeholder('e.g. 1 capsule'),
                            TextInput::make('frequency')
                                ->label('Frequency')
                                ->placeholder('e.g. 3x a day'),
                            TextInput::make('duration')
                                ->label('Duration')
                                ->placeholder('e.g. 7 days'),
                            Textarea::make('instructions')
                                ->label('Special Instructions')
                                ->rows(2)
                                ->placeholder('e.g. Take after meals')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->addActionLabel('Add Medication')
                        ->minItems(1)
                        ->defaultItems(1),
                ]),

            Section::make('Additional Notes')
                ->schema([
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Prescription Info')
                ->schema([
                    TextEntry::make('prescription_number')->label('Rx #')->copyable(),
                    TextEntry::make('prescribed_date')->date(),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('dentist.user.name')->label('Prescribing Dentist')
                        ->formatStateUsing(fn ($state) => 'Dr. ' . $state),
                    TextEntry::make('appointment.appointment_number')->label('Appointment')->placeholder('—'),
                    TextEntry::make('diagnosis')->placeholder('—')->columnSpanFull(),
                ])->columns(2),

            Section::make('Medications')
                ->schema([
                    RepeatableEntry::make('medications')
                        ->schema([
                            TextEntry::make('name')->label('Medicine'),
                            TextEntry::make('strength')->placeholder('—'),
                            TextEntry::make('form')->badge()->placeholder('—'),
                            TextEntry::make('dose')->label('Dose')->placeholder('—'),
                            TextEntry::make('frequency')->placeholder('—'),
                            TextEntry::make('duration')->placeholder('—'),
                            TextEntry::make('instructions')->label('Instructions')->placeholder('—')->columnSpanFull(),
                        ])->columns(3),
                ]),

            Section::make('Notes')
                ->schema([
                    TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                ])
                ->visible(fn (Prescription $record) => filled($record->notes)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prescription_number')->label('Rx #')->searchable()->sortable()->copyable(),
                TextColumn::make('prescribed_date')->date()->sortable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                TextColumn::make('dentist.user.name')->label('Dentist')->searchable(),
                TextColumn::make('medications')
                    ->label('Medications')
                    ->formatStateUsing(fn ($state) => collect($state)->pluck('name')->implode(', '))
                    ->limit(50),
                IconColumn::make('is_printed')->label('Printed')->boolean()->sortable(),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Prescription $record) => route('prescription.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('prescribed_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'view'   => Pages\ViewPrescription::route('/{record}'),
            'edit'   => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }
}
