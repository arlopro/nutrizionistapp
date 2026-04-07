<?php

namespace App\Enums;

enum ClientGoal: string
{
    case WeightLoss = 'weight_loss';
    case WeightGain = 'weight_gain';
    case Maintenance = 'maintenance';
    case MuscleGain = 'muscle_gain';
    case Health = 'health';

    public function label(): string
    {
        return match ($this) {
            self::WeightLoss => 'Perdita peso',
            self::WeightGain => 'Aumento peso',
            self::Maintenance => 'Mantenimento',
            self::MuscleGain => 'Aumento massa muscolare',
            self::Health => 'Salute generale',
        };
    }
}
