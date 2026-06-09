<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'Appointments';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'appointment_number';

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_appointment'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_appointment'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_appointment'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_appointment'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_appointment'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_appointment'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Appointment Details')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name . ' (' . $record->patient_number . ')')
                        ->searchable(['first_name', 'last_name', 'patient_number'])
                        ->preload()
                        ->required(),

                    Select::make('dentist_id')
                        ->label('Dentist')
                        ->relationship('dentist', 'id')
                        ->getOptionLabelFromRecordUsing(fn (Dentist $record) => $record->full_name)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),

                    Select::make('service_id')
                        ->label('Service')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if ($state && $service = Service::find($state)) {
                                $startTime = $get('start_time');
                                if ($startTime) {
                                    $set('end_time', date('H:i', strtotime($startTime) + $service->duration_minutes * 60));
                                }
                            }
                        }),

                    Select::make('type')
                        ->options(AppointmentType::class)
                        ->default(AppointmentType::Consultation->value)
                        ->required(),

                    DatePicker::make('appointment_date')
                        ->required()
                        ->minDate(today()),

                    TimePicker::make('start_time')
                        ->required()
                        ->seconds(false)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if ($state && $serviceId = $get('service_id')) {
                                if ($service = Service::find($serviceId)) {
                                    $set('end_time', date('H:i', strtotime($state) + $service->duration_minutes * 60));
                                }
                            }
                        }),

                    TimePicker::make('end_time')
                        ->required()
                        ->seconds(false),

                    Select::make('status')
                        ->options(AppointmentStatus::class)
                        ->default(AppointmentStatus::Pending->value)
                        ->required(),
                ])->columns(2),

            Section::make('Clinical Notes')
                ->schema([
                    Textarea::make('chief_complaint')->rows(3),
                    Textarea::make('notes')->rows(3),
                    Textarea::make('cancellation_reason')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('status') === AppointmentStatus::Cancelled->value),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Appointment Details')
                ->schema([
                    TextEntry::make('appointment_number')->label('Appointment #')->copyable(),
                    TextEntry::make('status')->badge()
                        ->color(fn (AppointmentStatus $state): string => $state->color()),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('dentist.user.name')->label('Dentist')
                        ->formatStateUsing(fn ($state) => 'Dr. ' . $state),
                    TextEntry::make('service.display_name')->label('Service'),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('appointment_date')->date(),
                    TextEntry::make('start_time')->time()
                        ->label('Time')
                        ->formatStateUsing(fn ($record) => date('g:i A', strtotime($record->start_time)) . ' – ' . date('g:i A', strtotime($record->end_time))),
                ])->columns(2),

            Section::make('Clinical Notes')
                ->schema([
                    TextEntry::make('chief_complaint')->label('Chief Complaint')->placeholder('—'),
                    TextEntry::make('notes')->placeholder('—'),
                    TextEntry::make('cancellation_reason')->placeholder('—')
                        ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Cancelled),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_number')->searchable()->sortable()->copyable(),
                TextColumn::make('appointment_date')->date()->sortable(),
                TextColumn::make('start_time')->time()->sortable(),
                TextColumn::make('patient.full_name')->searchable(['patient.first_name', 'patient.last_name'])->label('Patient'),
                TextColumn::make('dentist.user.name')->label('Dentist')->searchable(),
                TextColumn::make('service.display_name')->label('Service'),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('status')->options(AppointmentStatus::class),
                SelectFilter::make('type')->options(AppointmentType::class),
                SelectFilter::make('dentist_id')
                    ->label('Dentist')
                    ->relationship('dentist.user', 'name'),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                    ->action(fn (Appointment $record) => $record->confirm()),

                Action::make('cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => \in_array($record->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed], true))
                    ->action(fn (Appointment $record) => $record->cancel()),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('appointment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view'   => Pages\ViewAppointment::route('/{record}'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['patient', 'dentist.user', 'service.category']);
    }
}
