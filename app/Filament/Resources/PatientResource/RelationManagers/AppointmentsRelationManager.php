<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Enums\AppointmentStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()->with(['service.category', 'dentist.user']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('appointment_number')
            ->columns([
                TextColumn::make('appointment_number')->searchable(),
                TextColumn::make('appointment_date')->date()->sortable(),
                TextColumn::make('start_time')->time(),
                TextColumn::make('dentist.user.name')->label('Dentist'),
                TextColumn::make('service.display_name')->label('Service'),
                TextColumn::make('status')->badge()->sortable()
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
            ])
            ->defaultSort('appointment_date', 'desc');
    }
}
