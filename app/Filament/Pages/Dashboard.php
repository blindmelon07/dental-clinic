<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppointmentStatsWidget;
use App\Filament\Widgets\BillingStatsWidget;
use App\Filament\Widgets\CleaningRemindersWidget;
use App\Filament\Widgets\InventoryStatsWidget;
use App\Filament\Widgets\LowStockMedicinesWidget;
use App\Filament\Widgets\RecentAppointmentsWidget;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            AppointmentStatsWidget::class,
            BillingStatsWidget::class,
            InventoryStatsWidget::class,

            RecentAppointmentsWidget::class,
            CleaningRemindersWidget::class,
            LowStockMedicinesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
