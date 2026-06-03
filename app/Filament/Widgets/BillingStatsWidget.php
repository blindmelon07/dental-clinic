<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Billing & Revenue';
    protected ?string $description = 'Monthly collections, outstanding balances, and payment status';

    protected function getStats(): array
    {
        $monthRevenue = Payment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $lastMonthRevenue = Payment::whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');

        $outstanding = Invoice::whereNotIn('status', [
                InvoiceStatus::Paid,
                InvoiceStatus::Cancelled,
            ])
            ->sum('balance_due');

        $paidThisMonth = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count();

        $overdueCount = Invoice::where('status', InvoiceStatus::Overdue)->count();

        $partialCount = Invoice::where('status', InvoiceStatus::PartiallyPaid)->count();

        $trend = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $trendDesc = $lastMonthRevenue > 0
            ? ($trend >= 0 ? "↑ {$trend}% vs last month" : "↓ " . abs($trend) . "% vs last month")
            : 'No data for last month';

        return [
            Stat::make('Monthly Revenue', '₱' . number_format($monthRevenue, 2))
                ->description($trendDesc)
                ->icon('heroicon-o-banknotes')
                ->color($trend >= 0 ? 'success' : 'danger'),

            Stat::make('Outstanding Balance', '₱' . number_format($outstanding, 2))
                ->description('Unpaid & partially paid invoices')
                ->icon('heroicon-o-exclamation-circle')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make('Fully Paid This Month', $paidThisMonth)
                ->description('Invoices settled in ' . now()->format('F'))
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Overdue Invoices', $overdueCount)
                ->description('Past due date with balance')
                ->icon('heroicon-o-clock')
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Partial Payments', $partialCount)
                ->description('Invoices with remaining balance')
                ->icon('heroicon-o-currency-dollar')
                ->color($partialCount > 0 ? 'warning' : 'success'),
        ];
    }
}
