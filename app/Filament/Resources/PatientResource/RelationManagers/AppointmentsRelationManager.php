<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public function infolist(Schema $schema): Schema
    {
        return AppointmentResource::infolist($schema);
    }

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
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::FourExtraLarge)
                    ->visible(fn () => auth()->user()?->can('view_appointment')),
            ])
            ->recordAction('view')
            ->defaultSort('appointment_date', 'desc');
    }
}
