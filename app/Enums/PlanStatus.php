<?php

namespace App\Enums;

enum PlanStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza',
            self::Active => 'Attivo',
            self::Completed => 'Completato',
            self::Archived => 'Archiviato',
        };
    }
}
