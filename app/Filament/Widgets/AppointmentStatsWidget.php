<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayAppointments = Appointment::whereDate('appointment_date', today())->count();
        $pendingAppointments = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalPatients = Patient::where('is_active', true)->count();
        $monthRevenue = Invoice::whereMonth('invoice_date', now()->month)->sum('amount_paid');

        return [
            Stat::make("Today's Appointments", $todayAppointments)
                ->description('Scheduled for today')
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Pending Appointments', $pendingAppointments)
                ->description('Awaiting confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Active Patients', $totalPatients)
                ->description('Registered patients')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Monthly Revenue', '₱' . number_format($monthRevenue, 2))
                ->description('Collected this month')
                ->icon('heroicon-o-banknotes')
                ->color('info'),
        ];
    }
}
