<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programmato',
            self::Confirmed => 'Confermato',
            self::Completed => 'Completato',
            self::Cancelled => 'Cancellato',
            self::NoShow => 'Non presentato',
        };
    }
}
