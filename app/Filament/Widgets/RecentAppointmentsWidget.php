<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Recent & Upcoming Appointments';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Appointment::with(['patient', 'dentist.user', 'service.category'])
                    ->whereDate('appointment_date', '>=', today()->subDays(7))
                    ->whereDate('appointment_date', '<=', today()->addDays(7))
                    ->orderBy('appointment_date')
                    ->orderBy('start_time')
            )
            ->columns([
                TextColumn::make('appointment_date')->date()->sortable(),
                TextColumn::make('start_time')->time(),
                TextColumn::make('patient.full_name')->label('Patient'),
                TextColumn::make('dentist.user.name')->label('Dentist'),
                TextColumn::make('service.display_name')->label('Service'),
                TextColumn::make('status')->badge()
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
            ]);
    }
}
