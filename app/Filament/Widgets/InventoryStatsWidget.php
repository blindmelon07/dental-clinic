<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use App\Models\MedicineDispensing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'Medicine Inventory';
    protected ?string $description = 'Stock levels, expiry dates, and dispensing activity';

    protected function getStats(): array
    {
        $total = Medicine::where('is_active', true)->count();

        $lowStock = Medicine::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $outOfStock = Medicine::where('is_active', true)
            ->where('current_stock', 0)
            ->count();

        $expiringSoon = Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();

        $expired = Medicine::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();

        $dispensedThisMonth = MedicineDispensing::whereMonth('dispensed_at', now()->month)
            ->whereYear('dispensed_at', now()->year)
            ->count();

        return [
            Stat::make('Total Medicines', $total)
                ->description('Active items in inventory')
                ->icon('heroicon-o-beaker')
                ->color('primary'),

            Stat::make('Low Stock', $lowStock)
                ->description('At or below minimum level')
                ->icon('heroicon-o-arrow-trending-down')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Out of Stock', $outOfStock)
                ->description('Zero units remaining')
                ->icon('heroicon-o-x-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),

            Stat::make('Expiring Soon', $expiringSoon)
                ->description('Expires within 30 days')
                ->icon('heroicon-o-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),

            Stat::make('Expired', $expired)
                ->description('Past expiry date')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($expired > 0 ? 'danger' : 'success'),

            Stat::make('Dispensed This Month', $dispensedThisMonth)
                ->description('Transactions in ' . now()->format('F'))
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
        ];
    }
}
