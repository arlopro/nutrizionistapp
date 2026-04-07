<?php

namespace App\Services;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\Gender;

class TdeeCalculator
{
    /**
     * Mifflin-St Jeor BMR (kcal/day).
     */
    public static function bmrMifflin(float $weightKg, float $heightCm, int $age, Gender $gender): float
    {
        $base = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);

        return round($gender === Gender::Male ? $base + 5 : $base - 161, 0);
    }

    /**
     * Harris-Benedict revised BMR (kcal/day).
     */
    public static function bmrHarrisBenedict(float $weightKg, float $heightCm, int $age, Gender $gender): float
    {
        if ($gender === Gender::Male) {
            return round(88.362 + (13.397 * $weightKg) + (4.799 * $heightCm) - (5.677 * $age), 0);
        }

        return round(447.593 + (9.247 * $weightKg) + (3.098 * $heightCm) - (4.330 * $age), 0);
    }

    /**
     * TDEE = BMR × activity multiplier.
     */
    public static function tdee(float $bmr, ActivityLevel $activityLevel): float
    {
        return round($bmr * $activityLevel->multiplier(), 0);
    }

    /**
     * Suggest daily calories based on goal.
     */
    public static function goalCalories(float $tdee, ?ClientGoal $goal): float
    {
        return round(match ($goal) {
            ClientGoal::WeightLoss, ClientGoal::WeightClassTarget => $tdee * 0.80,
            ClientGoal::WeightGain, ClientGoal::MuscleGain => $tdee * 1.15,
            ClientGoal::SportPerformance, ClientGoal::EnduranceImprovement => $tdee * 1.10,
            default => $tdee,
        }, 0);
    }

    /**
     * Suggest macro split (g) from target calories.
     * Default: 30% protein, 40% carbs, 30% fat.
     */
    public static function macros(float $calories): array
    {
        return [
            'protein_grams' => round(($calories * 0.30) / 4, 0),
            'carbs_grams'   => round(($calories * 0.40) / 4, 0),
            'fat_grams'     => round(($calories * 0.30) / 9, 0),
        ];
    }

    /**
     * Full calculation from client profile data.
     */
    public static function calculate(
        float $weightKg,
        float $heightCm,
        int $age,
        Gender $gender,
        ActivityLevel $activityLevel,
        ?ClientGoal $goal = null,
        string $formula = 'mifflin',
    ): array {
        $bmr = $formula === 'harris_benedict'
            ? self::bmrHarrisBenedict($weightKg, $heightCm, $age, $gender)
            : self::bmrMifflin($weightKg, $heightCm, $age, $gender);

        $tdee = self::tdee($bmr, $activityLevel);
        $targetCalories = self::goalCalories($tdee, $goal);
        $macros = self::macros($targetCalories);

        return [
            'formula'          => $formula,
            'bmr'              => $bmr,
            'tdee'             => $tdee,
            'goal'             => $goal?->value,
            'daily_calories'   => $targetCalories,
            ...$macros,
        ];
    }
}
