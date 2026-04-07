<?php

namespace App\Enums;

enum FoodCategory: string
{
    case Protein = 'protein';
    case Carbohydrate = 'carbohydrate';
    case Vegetable = 'vegetable';
    case Fruit = 'fruit';
    case Dairy = 'dairy';
    case Fat = 'fat';
    case Generic = 'generic';

    public function label(): string
    {
        return match ($this) {
            self::Protein => 'Proteine',
            self::Carbohydrate => 'Carboidrati',
            self::Vegetable => 'Verdure',
            self::Fruit => 'Frutta',
            self::Dairy => 'Latticini',
            self::Fat => 'Grassi',
            self::Generic => 'Generico',
        };
    }
}
