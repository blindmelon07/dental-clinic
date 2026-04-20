<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppointmentStatsWidget;
use App\Filament\Widgets\RecentAppointmentsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            AppointmentStatsWidget::class,
            RevenueChartWidget::class,
            RecentAppointmentsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
