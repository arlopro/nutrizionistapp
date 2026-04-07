<?php

namespace App\Enums;

enum ClientGoal: string
{
    case WeightLoss = 'weight_loss';
    case WeightGain = 'weight_gain';
    case Maintenance = 'maintenance';
    case MuscleGain = 'muscle_gain';
    case Health = 'health';
    case SportPerformance = 'sport_performance';
    case EnduranceImprovement = 'endurance_improvement';
    case WeightClassTarget = 'weight_class_target';
    case GutBalance = 'gut_balance';
    case InflammationReduction = 'inflammation_reduction';
    case HormonalSupport = 'hormonal_support';
    case MetabolicSyndrome = 'metabolic_syndrome';
    case SiboProtocol = 'sibo_protocol';

    public function label(): string
    {
        return match ($this) {
            self::WeightLoss => 'Perdita peso',
            self::WeightGain => 'Aumento peso',
            self::Maintenance => 'Mantenimento',
            self::MuscleGain => 'Aumento massa muscolare',
            self::Health => 'Salute generale',
            self::SportPerformance => 'Performance sportiva',
            self::EnduranceImprovement => 'Miglioramento resistenza',
            self::WeightClassTarget => 'Obiettivo categoria peso',
            self::GutBalance => 'Equilibrio intestinale',
            self::InflammationReduction => 'Riduzione infiammazione',
            self::HormonalSupport => 'Supporto ormonale',
            self::MetabolicSyndrome => 'Sindrome metabolica',
            self::SiboProtocol => 'Protocollo SIBO',
        };
    }
}
