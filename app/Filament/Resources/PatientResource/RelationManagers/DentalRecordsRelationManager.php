<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Filament\Resources\DentalRecordResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DentalRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'dentalRecords';

    public function infolist(Schema $schema): Schema
    {
        return DentalRecordResource::infolist($schema);
    }

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
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->visible(fn () => auth()->user()?->can('view_dental_record')),
            ])
            ->recordAction('view')
            ->defaultSort('visit_date', 'desc');
    }
}
