<?php

namespace Database\Factories;

use App\Models\CheckIn;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CheckIn> */
class CheckInFactory extends Factory
{
    private static array $notes = [
        'Mi sento molto meglio questa settimana.',
        'Ho avuto qualche difficoltà con i pasti serali.',
        'Ottima settimana, ho seguito il piano al 100%.',
        'Un po\' di stanchezza, ma continuo con impegno.',
        'Ho mangiato fuori due volte, cercato di stare attento.',
        'Finalmente comincio a vedere i risultati!',
        'Settimana stressante, ho avuto qualche sgarro.',
        'Molto motivata, sto seguendo tutto alla lettera.',
        'Ho aumentato l\'attività fisica questa settimana.',
        'Niente di particolare da segnalare, tutto regolare.',
        'Ho dormito meglio, mi sento più energica.',
        'Un po\' di gonfiore addominale, ma peso stabile.',
    ];

    public function definition(): array
    {
        return [
            'client_id'    => null,
            'date'         => fake()->dateTimeBetween('-5 months', 'now')->format('Y-m-d'),
            'weight_kg'    => null, // set in seeder based on progression
            'body_fat_percentage' => fake()->boolean(40) ? fake()->randomFloat(1, 12.0, 35.0) : null,
            'lean_mass_kg' => fake()->boolean(40) ? fake()->randomFloat(1, 35.0, 75.0) : null,
            'body_water_percentage' => fake()->boolean(30) ? fake()->randomFloat(1, 45.0, 65.0) : null,
            'skinfold_triceps' => fake()->boolean(25) ? fake()->randomFloat(1, 5.0, 35.0) : null,
            'skinfold_biceps' => fake()->boolean(25) ? fake()->randomFloat(1, 3.0, 20.0) : null,
            'skinfold_subscapular' => fake()->boolean(25) ? fake()->randomFloat(1, 5.0, 30.0) : null,
            'skinfold_suprailiac' => fake()->boolean(25) ? fake()->randomFloat(1, 5.0, 35.0) : null,
            'mood'         => fake()->numberBetween(2, 5),
            'energy_level' => fake()->numberBetween(2, 5),
            'sleep_quality' => fake()->numberBetween(2, 5),
            'water_liters' => fake()->randomFloat(1, 1.0, 3.0),
            'notes'        => fake()->boolean(60) ? fake()->randomElement(self::$notes) : null,
            'nutritionist_notes' => null,
        ];
    }
}
