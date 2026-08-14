<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DentalRecordResource;
use App\Models\DentalRecord;
use App\Models\PaymentPlanInstallment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentPlanStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Installment Plans';
    protected ?string $description = 'Dental visits on a payment plan and their installment status';

    protected function getStats(): array
    {
        $activePlans = DentalRecord::whereHas('paymentPlan')->count();

        $dueThisMonth = PaymentPlanInstallment::whereNull('payment_id')
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->count();

        $overdue = PaymentPlanInstallment::whereNull('payment_id')
            ->where('due_date', '<', today())
            ->count();

        $outstanding = PaymentPlanInstallment::whereNull('payment_id')->sum('amount');

        $patientsWithBalance = DentalRecord::whereHas('installments', fn ($q) => $q->whereNull('payment_id'))
            ->distinct('patient_id')
            ->count('patient_id');

        return [
            Stat::make('Active Payment Plans', $activePlans)
                ->description('Dental records on an installment plan')
                ->icon('heroicon-o-calendar-date-range')
                ->color('primary')
                ->url(DentalRecordResource::getUrl('index', [
                    'filters' => ['has_payment_plan' => ['value' => '1']],
                ])),

            Stat::make('Installments Due This Month', $dueThisMonth)
                ->description('Unpaid, due in ' . now()->format('F'))
                ->icon('heroicon-o-clock')
                ->color($dueThisMonth > 0 ? 'warning' : 'success')
                ->url(DentalRecordResource::getUrl('index', [
                    'filters' => ['installments_due_this_month' => ['value' => '1']],
                ])),

            Stat::make('Overdue Installments', $overdue)
                ->description('Past due date, unpaid')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url(DentalRecordResource::getUrl('index', [
                    'filters' => ['installments_overdue' => ['value' => '1']],
                ])),

            Stat::make('Outstanding on Plans', '₱' . number_format($outstanding, 2))
                ->description('Unpaid installments across all plans')
                ->icon('heroicon-o-banknotes')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make('Patients with Balance', $patientsWithBalance)
                ->description('Distinct patients with unpaid installments')
                ->icon('heroicon-o-user-group')
                ->color($patientsWithBalance > 0 ? 'warning' : 'success')
                ->url(DentalRecordResource::getUrl('index', [
                    'filters' => ['installments_unpaid' => ['value' => '1']],
                ])),
        ];
    }
}
