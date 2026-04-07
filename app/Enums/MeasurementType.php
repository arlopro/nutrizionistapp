<?php

namespace App\Enums;

enum MeasurementType: string
{
    case Waist = 'waist';
    case Hips = 'hips';
    case Chest = 'chest';
    case ArmLeft = 'arm_left';
    case ArmRight = 'arm_right';
    case ThighLeft = 'thigh_left';
    case ThighRight = 'thigh_right';
    case Calf = 'calf';
    case Neck = 'neck';

    public function label(): string
    {
        return match ($this) {
            self::Waist => 'Vita',
            self::Hips => 'Fianchi',
            self::Chest => 'Petto',
            self::ArmLeft => 'Braccio sinistro',
            self::ArmRight => 'Braccio destro',
            self::ThighLeft => 'Coscia sinistra',
            self::ThighRight => 'Coscia destra',
            self::Calf => 'Polpaccio',
            self::Neck => 'Collo',
        };
    }
}
