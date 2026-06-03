<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockMedicinesWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Low Stock Medicines';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Medicine::query()
                    ->whereColumn('current_stock', '<=', 'minimum_stock')
                    ->where('is_active', true)
                    ->orderBy('current_stock')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Medicine')
                    ->description(fn (Medicine $r): string => implode(' · ', array_filter([$r->generic_name, $r->strength]))),

                TextColumn::make('form')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn (Medicine $r): string => $r->current_stock . ' ' . $r->unit),

                TextColumn::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->formatStateUsing(fn (Medicine $r): string => $r->minimum_stock . ' ' . $r->unit),

                TextColumn::make('expiry_date')
                    ->label('Expiry')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->color(fn (Medicine $r): ?string =>
                        $r->isExpired() ? 'danger' : ($r->isExpiringSoon() ? 'warning' : null)
                    ),
            ])
            ->emptyStateHeading('All medicines are sufficiently stocked')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
