<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Attivo',
            self::Inactive => 'Inattivo',
            self::Archived => 'Archiviato',
        };
    }
}
