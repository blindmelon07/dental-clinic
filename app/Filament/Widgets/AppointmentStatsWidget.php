<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'Clinic Overview';
    protected ?string $description = 'Appointments, patients, and reminders at a glance';

    protected function getStats(): array
    {
        $todayAppointments   = Appointment::whereDate('appointment_date', today())->count();
        $pendingAppointments = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalPatients       = Patient::where('is_active', true)->count();
        $cleaningsDue        = Patient::whereNotNull('next_cleaning_due')
            ->where('next_cleaning_due', '<=', now()->addDays(30))
            ->where('is_active', true)
            ->count();

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

            Stat::make('Cleaning Reminders', $cleaningsDue)
                ->description('Due or overdue within 30 days')
                ->icon('heroicon-o-sparkles')
                ->color($cleaningsDue > 0 ? 'danger' : 'success'),
        ];
    }
}
