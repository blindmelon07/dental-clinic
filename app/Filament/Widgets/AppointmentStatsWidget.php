<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\PatientResource;
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
                ->color('primary')
                ->url(AppointmentResource::getUrl('index', [
                    'filters' => ['appointment_date' => [
                        'from'  => today()->toDateString(),
                        'until' => today()->toDateString(),
                    ]],
                ])),

            Stat::make('Pending Appointments', $pendingAppointments)
                ->description('Awaiting confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(AppointmentResource::getUrl('index', [
                    'filters' => ['status' => ['value' => AppointmentStatus::Pending->value]],
                ])),

            Stat::make('Total Active Patients', $totalPatients)
                ->description('Registered patients')
                ->icon('heroicon-o-users')
                ->color('success')
                ->url(PatientResource::getUrl('index', [
                    'filters' => ['is_active' => ['value' => '1']],
                ])),

            Stat::make('Cleaning Reminders', $cleaningsDue)
                ->description('Due or overdue within 30 days')
                ->icon('heroicon-o-sparkles')
                ->color($cleaningsDue > 0 ? 'danger' : 'success')
                ->url(PatientResource::getUrl('index', [
                    'filters' => ['cleaning_due' => ['value' => '1']],
                ])),
        ];
    }
}
