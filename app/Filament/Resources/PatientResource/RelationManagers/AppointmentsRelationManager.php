<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['service.category', 'dentist.user']))
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
