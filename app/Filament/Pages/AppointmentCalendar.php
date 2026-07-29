<?php

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Dentist;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AppointmentCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null $navigationGroup = 'Appointments';
    protected static ?string $navigationLabel = 'Calendar';
    protected static ?int $navigationSort = 0;
    protected string $view = 'filament.pages.appointment-calendar';

    private const DAY_START_HOUR = 8;
    private const DAY_END_HOUR = 18;

    public string $weekStart;
    public ?int $dentistFilter = null;
    public ?string $statusFilter = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_appointment') ?? false;
    }

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function getWeekDays(): array
    {
        $start = Carbon::parse($this->weekStart);

        return collect(range(0, 6))
            ->map(fn (int $i) => $start->copy()->addDays($i))
            ->all();
    }

    public function getWeekRangeLabel(): string
    {
        $start = Carbon::parse($this->weekStart);
        $end = $start->copy()->addDays(6);

        return $start->format('M j') . ' – ' . $end->format('M j, Y');
    }

    public function getHours(): array
    {
        return range(self::DAY_START_HOUR, self::DAY_END_HOUR - 1);
    }

    public function getGridHeight(): int
    {
        return (self::DAY_END_HOUR - self::DAY_START_HOUR) * 60;
    }

    public function getDentistOptions(): array
    {
        return Dentist::where('is_active', true)
            ->with('user')
            ->get()
            ->mapWithKeys(fn (Dentist $dentist) => [$dentist->id => $dentist->user->name])
            ->all();
    }

    public function getStatusOptions(): array
    {
        return collect(AppointmentStatus::cases())
            ->mapWithKeys(fn (AppointmentStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    public function getAppointmentsForDay(Carbon $day): Collection
    {
        return Appointment::whereDate('appointment_date', $day)
            ->when($this->dentistFilter, fn ($query) => $query->where('dentist_id', $this->dentistFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->with(['patient', 'dentist.user', 'service'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Lays out a day's appointments into non-overlapping columns and returns
     * each one with the top/height/left/width needed to absolutely position it.
     */
    public function layoutDay(Collection $appointments): array
    {
        $events = $appointments
            ->map(fn (Appointment $appointment) => [
                'appointment' => $appointment,
                'start'       => $this->timeToMinutes($appointment->start_time),
                'end'         => $this->timeToMinutes($appointment->end_time),
            ])
            ->sortBy('start')
            ->values();

        $groups = [];
        $currentGroup = [];
        $currentGroupEnd = null;

        foreach ($events as $event) {
            if ($currentGroupEnd !== null && $event['start'] >= $currentGroupEnd) {
                $groups[] = $currentGroup;
                $currentGroup = [];
                $currentGroupEnd = null;
            }

            $currentGroup[] = $event;
            $currentGroupEnd = $currentGroupEnd === null ? $event['end'] : max($currentGroupEnd, $event['end']);
        }

        if (! empty($currentGroup)) {
            $groups[] = $currentGroup;
        }

        $dayStartMinutes = self::DAY_START_HOUR * 60;
        $result = [];

        foreach ($groups as $group) {
            $columnEnds = [];
            $eventColumn = [];

            foreach ($group as $idx => $event) {
                $placed = false;

                foreach ($columnEnds as $colIdx => $colEnd) {
                    if ($event['start'] >= $colEnd) {
                        $columnEnds[$colIdx] = $event['end'];
                        $eventColumn[$idx] = $colIdx;
                        $placed = true;
                        break;
                    }
                }

                if (! $placed) {
                    $columnEnds[] = $event['end'];
                    $eventColumn[$idx] = count($columnEnds) - 1;
                }
            }

            $numColumns = count($columnEnds);

            foreach ($group as $idx => $event) {
                $result[] = [
                    'appointment' => $event['appointment'],
                    'top'         => $event['start'] - $dayStartMinutes,
                    'height'      => max($event['end'] - $event['start'], 22),
                    'left'        => ($eventColumn[$idx] / $numColumns) * 100,
                    'width'       => (100 / $numColumns) - 1,
                ];
            }
        }

        return $result;
    }

    public function appointmentUrl(Appointment $appointment): string
    {
        return AppointmentResource::getUrl('view', ['record' => $appointment]);
    }

    public function viewAppointmentAction(): Action
    {
        return Action::make('viewAppointment')
            ->modalHeading(fn (?Appointment $record) => $record ? "Appointment {$record->appointment_number}" : 'Appointment')
            ->modalWidth('2xl')
            ->record(fn (array $arguments): ?Appointment => Appointment::find($arguments['id'] ?? null))
            ->infolist(fn (Schema $schema) => AppointmentResource::infolist($schema))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->extraModalFooterActions(fn (?Appointment $record): array => $record ? [
                Action::make('editAppointment')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn () => AppointmentResource::getUrl('edit', ['record' => $record])),
            ] : []);
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 60) + $minute;
    }
}
