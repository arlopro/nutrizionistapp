<?php

namespace App\Enums;

enum AppointmentType: string
{
    case FirstVisit = 'first_visit';
    case FollowUp = 'follow_up';
    case Online = 'online';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FirstVisit => 'Prima visita',
            self::FollowUp => 'Controllo',
            self::Online => 'Online',
            self::Other => 'Altro',
        };
    }
}
