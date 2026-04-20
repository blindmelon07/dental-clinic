<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Confirmed  => 'Confirmed',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::NoShow     => 'No Show',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending    => 'warning',
            self::Confirmed  => 'info',
            self::InProgress => 'primary',
            self::Completed  => 'success',
            self::Cancelled  => 'danger',
            self::NoShow     => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Pending    => 'heroicon-o-clock',
            self::Confirmed  => 'heroicon-o-check-circle',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Completed  => 'heroicon-o-check-badge',
            self::Cancelled  => 'heroicon-o-x-circle',
            self::NoShow     => 'heroicon-o-user-minus',
        };
    }
}
