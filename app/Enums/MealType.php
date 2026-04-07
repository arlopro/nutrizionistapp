<?php

namespace App\Enums;

enum MealType: string
{
    case Breakfast = 'breakfast';
    case MorningSnack = 'morning_snack';
    case Lunch = 'lunch';
    case AfternoonSnack = 'afternoon_snack';
    case Dinner = 'dinner';
    case EveningSnack = 'evening_snack';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => 'Colazione',
            self::MorningSnack => 'Spuntino mattina',
            self::Lunch => 'Pranzo',
            self::AfternoonSnack => 'Spuntino pomeriggio',
            self::Dinner => 'Cena',
            self::EveningSnack => 'Spuntino sera',
        };
    }
}
