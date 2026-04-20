<?php

namespace App\Enums;

enum AppointmentType: string
{
    case Consultation = 'consultation';
    case FollowUp = 'follow_up';
    case Emergency = 'emergency';
    case Cleaning = 'cleaning';
    case Procedure = 'procedure';
    case XRay = 'xray';

    public function label(): string
    {
        return match($this) {
            self::Consultation => 'Consultation',
            self::FollowUp     => 'Follow-up',
            self::Emergency    => 'Emergency',
            self::Cleaning     => 'Cleaning',
            self::Procedure    => 'Procedure',
            self::XRay         => 'X-Ray',
        };
    }
}
