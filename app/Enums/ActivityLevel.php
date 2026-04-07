<?php

namespace App\Enums;

enum ActivityLevel: string
{
    case Sedentary = 'sedentary';
    case Light = 'light';
    case Moderate = 'moderate';
    case Active = 'active';
    case VeryActive = 'very_active';

    public function label(): string
    {
        return match ($this) {
            self::Sedentary => 'Sedentario',
            self::Light => 'Leggero',
            self::Moderate => 'Moderato',
            self::Active => 'Attivo',
            self::VeryActive => 'Molto attivo',
        };
    }
}
