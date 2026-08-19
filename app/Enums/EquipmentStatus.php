<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case Operational = 'operational';
    case UnderMaintenance = 'under_maintenance';
    case OutOfService = 'out_of_service';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Operational      => 'Operational',
            self::UnderMaintenance => 'Under Maintenance',
            self::OutOfService     => 'Out of Service',
            self::Retired          => 'Retired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Operational      => 'success',
            self::UnderMaintenance => 'warning',
            self::OutOfService     => 'danger',
            self::Retired          => 'gray',
        };
    }
}
