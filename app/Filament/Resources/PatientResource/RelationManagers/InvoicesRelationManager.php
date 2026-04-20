<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')->searchable(),
                TextColumn::make('invoice_date')->date()->sortable(),
                TextColumn::make('total')->money('PHP')->sortable(),
                TextColumn::make('amount_paid')->money('PHP'),
                TextColumn::make('balance_due')->money('PHP'),
                TextColumn::make('status')->badge()->sortable(),
            ])
            ->defaultSort('invoice_date', 'desc');
    }
}
