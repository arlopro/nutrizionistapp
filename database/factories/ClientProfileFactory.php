<?php

namespace Database\Factories;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\Gender;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClientProfile> */
class ClientProfileFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        $isFemale = $gender === Gender::Female;

        return [
            'user_id'            => null,
            'nutritionist_id'    => null,
            'date_of_birth'      => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'gender'             => $gender->value,
            'height_cm'          => $isFemale ? fake()->numberBetween(155, 178) : fake()->numberBetween(165, 192),
            'initial_weight_kg'  => $isFemale ? fake()->randomFloat(1, 48, 88) : fake()->randomFloat(1, 60, 105),
            'activity_level'     => fake()->randomElement(ActivityLevel::cases())->value,
            'goal'               => fake()->randomElement(ClientGoal::cases())->value,
            'status'             => fake()->randomElement(['active', 'active', 'active', 'inactive', 'archived']),
            'allergies'          => fake()->boolean(25) ? fake()->randomElements(['glutine', 'lattosio', 'uova', 'frutta a guscio', 'pesce', 'crostacei', 'soia', 'sesamo'], fake()->numberBetween(1, 3)) : null,
            'intolerances'       => fake()->boolean(20) ? fake()->randomElements(['lattosio', 'fruttosio', 'nichel', 'istamina'], fake()->numberBetween(1, 2)) : null,
            'dietary_preferences' => fake()->randomElement([null, null, null, 'Vegetariano', 'Vegano', 'Senza glutine', 'Mediterraneo']),
            'pathologies'         => fake()->randomElement([null, null, null, 'Ipertensione', 'Diabete tipo 2', 'Ipotiroidismo', 'Sindrome metabolica', 'Colesterolo alto']),
            'notes'              => fake()->boolean(40) ? fake('it_IT')->sentence(fake()->numberBetween(8, 20)) : null,
        ];
    }
}
