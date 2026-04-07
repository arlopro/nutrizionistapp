<?php

namespace Database\Factories;

use App\Models\NutritionalPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NutritionalPlan> */
class NutritionalPlanFactory extends Factory
{
    private static array $planNames = [
        'Piano Dimagrimento Graduale',
        'Piano Mantenimento Peso Forma',
        'Piano Massa Muscolare',
        'Piano Detox Primaverile',
        'Piano Ipocalorico Bilanciato',
        'Dieta Mediterranea Classica',
        'Piano Proteico Sportivo',
        'Piano Low Carb Moderato',
        'Alimentazione Equilibrata',
        'Piano Metabolismo Attivo',
        'Piano Tonificazione',
        'Regime Alimentare Salutare',
        'Piano Antiinfiammatorio',
        'Alimentazione Vegana Bilanciata',
        'Piano Fitness e Benessere',
    ];

    public function definition(): array
    {
        $calories = fake()->numberBetween(1400, 2600);
        $proteinPct = fake()->numberBetween(20, 35);
        $fatPct = fake()->numberBetween(25, 35);
        $carbPct = 100 - $proteinPct - $fatPct;

        $proteinG = round(($calories * $proteinPct / 100) / 4, 0);
        $fatG     = round(($calories * $fatPct / 100) / 9, 0);
        $carbG    = round(($calories * $carbPct / 100) / 4, 0);

        $startDate = fake()->dateTimeBetween('-6 months', '+1 month');
        $hasEnd    = fake()->boolean(70);

        return [
            'nutritionist_id'  => null,
            'client_id'        => null,
            'title'            => fake()->randomElement(self::$planNames),
            'description'      => fake()->boolean(60) ? fake('it_IT')->sentence(fake()->numberBetween(10, 25)) : null,
            'status'           => fake()->randomElement(['active', 'active', 'active', 'draft', 'completed', 'archived']),
            'start_date'       => $startDate->format('Y-m-d'),
            'end_date'         => $hasEnd ? (clone $startDate)->modify('+' . fake()->numberBetween(4, 16) . ' weeks')->format('Y-m-d') : null,
            'daily_calories'   => $calories,
            'protein_grams'    => $proteinG,
            'carbs_grams'      => $carbG,
            'fat_grams'        => $fatG,
            'is_template'      => false,
            'notes'            => null,
        ];
    }
}
