<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DentalRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'dentalRecords';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('visit_date')
            ->columns([
                TextColumn::make('visit_date')->date()->sortable(),
                TextColumn::make('dentist.user.name')->label('Dentist'),
                TextColumn::make('diagnosis')->limit(50),
                TextColumn::make('treatment_done')->limit(50),
            ])
            ->defaultSort('visit_date', 'desc');
    }
}
