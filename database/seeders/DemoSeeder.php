<?php

namespace Database\Seeders;

use App\Enums\MealType;
use App\Models\Appointment;
use App\Models\CheckIn;
use App\Models\ClientProfile;
use App\Models\Food;
use App\Models\NutritionalPlan;
use App\Models\NutritionistProfile;
use App\Models\PlanMeal;
use App\Models\PlanMealItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private static array $nutritionists = [
        ['name' => 'Marco',    'last_name' => 'Rossi',     'email' => 'marco.rossi@demo.it',     'business' => 'Studio Nutrizione Rossi',     'spec' => 'Nutrizione sportiva',          'city' => 'Roma',    'prov' => 'RM'],
        ['name' => 'Giulia',   'last_name' => 'Ferrari',   'email' => 'giulia.ferrari@demo.it',   'business' => 'Benessere & Nutrizione',      'spec' => 'Nutrizione clinica',           'city' => 'Milano',  'prov' => 'MI'],
        ['name' => 'Luca',     'last_name' => 'Esposito',  'email' => 'luca.esposito@demo.it',    'business' => 'Nutri & Vita',                'spec' => 'Nutrizione pediatrica',        'city' => 'Napoli',  'prov' => 'NA'],
        ['name' => 'Francesca','last_name' => 'Bianchi',   'email' => 'francesca.bianchi@demo.it','business' => 'Studio Alimentazione Bianchi','spec' => 'Nutrizione vegana e vegetariana','city' => 'Torino', 'prov' => 'TO'],
    ];

    public function run(): void
    {
        // Load all foods from DB
        $foods = Food::all();
        if ($foods->isEmpty()) {
            $this->command->warn('No foods found — run FoodSeeder first.');
            return;
        }

        foreach (self::$nutritionists as $data) {
            // Skip if already exists
            if (User::where('email', $data['email'])->exists()) {
                $this->command->info("Skipping existing nutritionist: {$data['email']}");
                continue;
            }

            $nutri = User::create([
                'name'       => $data['name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make('password'),
                'phone'      => fake('it_IT')->phoneNumber(),
                'email_verified_at' => now(),
            ]);
            $nutri->assignRole('nutritionist');

            NutritionistProfile::create([
                'user_id'        => $nutri->id,
                'business_name'  => $data['business'],
                'specialization' => $data['spec'],
                'city'           => $data['city'],
                'province'       => $data['prov'],
                'license_number' => 'NUT-' . fake()->numerify('####'),
            ]);

            $this->command->info("Created nutritionist: {$nutri->name} {$nutri->last_name}");

            // Create 8-12 clients per nutritionist
            $clientCount = fake()->numberBetween(8, 12);
            for ($i = 0; $i < $clientCount; $i++) {
                $this->createClient($nutri, $foods);
            }
        }

        $this->command->info('DemoSeeder completed.');
    }

    private function createClient(User $nutritionist, $foods): void
    {
        $isFemale = fake()->boolean(55);

        $clientUser = User::factory()->create([
            'name'      => $isFemale ? fake('it_IT')->firstNameFemale() : fake('it_IT')->firstNameMale(),
            'last_name' => fake('it_IT')->lastName(),
        ]);
        $clientUser->assignRole('client');

        $clientProfile = ClientProfile::factory()->create([
            'user_id'         => $clientUser->id,
            'nutritionist_id' => $nutritionist->id,
            'gender'          => $isFemale ? 'female' : 'male',
        ]);

        // Create 1-2 nutritional plans
        $planCount = fake()->numberBetween(1, 2);
        for ($p = 0; $p < $planCount; $p++) {
            $isActive = ($p === 0); // first plan is active
            $plan = NutritionalPlan::factory()->create([
                'nutritionist_id' => $nutritionist->id,
                'client_id'       => $clientProfile->id,
                'status'          => $isActive ? 'active' : fake()->randomElement(['completed', 'archived']),
            ]);

            $this->addMealsToPlan($plan, $foods);
        }

        // Create 4-12 appointments
        $appointmentCount = fake()->numberBetween(4, 12);
        Appointment::factory()->count($appointmentCount)->create([
            'nutritionist_id' => $nutritionist->id,
            'client_id'       => $clientProfile->id,
        ]);

        // Create 3-8 check-ins with weight progression
        $checkInCount = fake()->numberBetween(3, 8);
        $baseWeight = (float) $clientProfile->initial_weight_kg;
        $goal = $clientProfile->goal?->value ?? 'weight_loss';

        // Weight trend direction
        $weightDelta = match ($goal) {
            'weight_loss'  => -fake()->randomFloat(1, 0.2, 0.8),
            'weight_gain', 'muscle_gain' => fake()->randomFloat(1, 0.1, 0.5),
            default        => fake()->randomFloat(1, -0.2, 0.2),
        };

        $dates = collect(range(0, $checkInCount - 1))
            ->map(fn ($i) => now()->subWeeks($checkInCount - $i))
            ->sortBy(fn ($d) => $d->timestamp);

        foreach ($dates as $idx => $date) {
            $weight = round($baseWeight + ($weightDelta * $idx) + fake()->randomFloat(1, -0.3, 0.3), 1);
            CheckIn::factory()->create([
                'client_id' => $clientProfile->id,
                'date'      => $date->format('Y-m-d'),
                'weight_kg' => max(40.0, $weight),
            ]);
        }
    }

    private function addMealsToPlan(NutritionalPlan $plan, $foods): void
    {
        $mealTypes = [
            MealType::Breakfast,
            MealType::Lunch,
            MealType::AfternoonSnack,
            MealType::Dinner,
        ];

        // Add meals for each day of week (0=Mon ... 6=Sun)
        // For simplicity, only days 0-4 (Mon-Fri) to keep data manageable
        $daysToSeed = fake()->randomElement([[0,1,2,3,4], [0,1,2,3,4,5,6]]);

        foreach ($daysToSeed as $day) {
            $sortOrder = 0;
            foreach ($mealTypes as $mealType) {
                $meal = PlanMeal::create([
                    'nutritional_plan_id' => $plan->id,
                    'meal_type'           => $mealType->value,
                    'day_of_week'         => $day,
                    'sort_order'          => $sortOrder++,
                ]);

                $this->addItemsToMeal($meal, $mealType, $foods);
            }
        }
    }

    private function addItemsToMeal(PlanMeal $meal, MealType $mealType, $foods): void
    {
        $itemCount = match ($mealType) {
            MealType::Breakfast      => fake()->numberBetween(2, 4),
            MealType::Lunch          => fake()->numberBetween(3, 5),
            MealType::Dinner         => fake()->numberBetween(3, 4),
            MealType::AfternoonSnack => fake()->numberBetween(1, 2),
            default                  => 2,
        };

        $randomFoods = $foods->random(min($itemCount, $foods->count()));

        foreach ($randomFoods as $idx => $food) {
            $grams = $this->randomGrams($food->category?->value ?? 'generic', $mealType);
            PlanMealItem::create([
                'plan_meal_id'   => $meal->id,
                'food_id'        => $food->id,
                'quantity_grams' => $grams,
                'sort_order'     => $idx,
                'notes'          => null,
                'alternative_of' => null,
            ]);
        }
    }

    private function randomGrams(string $category, MealType $mealType): float
    {
        return (float) match ($category) {
            'carbohydrate'         => fake()->randomElement([50, 60, 70, 80, 100]),
            'protein'              => fake()->randomElement([100, 120, 150, 180, 200]),
            'dairy'                => fake()->randomElement([100, 125, 150, 200, 250]),
            'vegetable', 'fruit'   => fake()->randomElement([100, 150, 200, 250]),
            'fat'                  => fake()->randomElement([10, 15, 20]),
            default                => fake()->randomElement([50, 80, 100, 120]),
        };
    }
}
