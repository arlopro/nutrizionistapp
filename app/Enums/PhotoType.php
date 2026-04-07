<?php

namespace App\Enums;

enum PhotoType: string
{
    case Front = 'front';
    case Side = 'side';
    case Back = 'back';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Front => 'Frontale',
            self::Side => 'Laterale',
            self::Back => 'Posteriore',
            self::Other => 'Altro',
        };
    }
}
