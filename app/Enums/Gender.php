<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Maschio',
            self::Female => 'Femmina',
            self::Other => 'Altro',
        };
    }
}
