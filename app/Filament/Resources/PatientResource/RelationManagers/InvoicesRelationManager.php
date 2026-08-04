<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function infolist(Schema $schema): Schema
    {
        return InvoiceResource::infolist($schema);
    }

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
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::FiveExtraLarge)
                    ->visible(fn () => auth()->user()?->can('view_invoice')),
            ])
            ->recordAction('view')
            ->defaultSort('invoice_date', 'desc');
    }
}
