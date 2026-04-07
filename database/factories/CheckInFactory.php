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
            'mood'         => fake()->numberBetween(2, 5),
            'energy_level' => fake()->numberBetween(2, 5),
            'sleep_quality' => fake()->numberBetween(2, 5),
            'water_liters' => fake()->randomFloat(1, 1.0, 3.0),
            'notes'        => fake()->boolean(60) ? fake()->randomElement(self::$notes) : null,
            'nutritionist_notes' => null,
        ];
    }
}
