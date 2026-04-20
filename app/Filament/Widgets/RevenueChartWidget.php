<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $heading = 'Monthly Revenue';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $revenues = $months->map(
            fn ($month) => Invoice::whereYear('invoice_date', $month->year)
                ->whereMonth('invoice_date', $month->month)
                ->sum('amount_paid')
        );

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (₱)',
                    'data'            => $revenues->values()->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.3)',
                    'borderColor'     => 'rgba(59, 130, 246, 1)',
                    'fill'            => true,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
