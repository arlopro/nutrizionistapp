<?php

namespace App\Http\Controllers\Client;

use App\Enums\MealType;
use App\Http\Controllers\Controller;
use App\Models\MealCompletion;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $clientProfile = $request->user()->clientProfile;

        abort_unless($clientProfile, 404);

        $plan = $clientProfile->nutritionalPlans()
            ->where('status', 'active')
            ->with(['meals.items.food', 'meals.items.recipe.ingredients.food'])
            ->latest()
            ->first();

        $recipes = [];
        if ($plan) {
            $recipeIds = $plan->meals->flatMap(fn ($m) => $m->items->pluck('recipe_id'))->filter()->unique();
            if ($recipeIds->isNotEmpty()) {
                $recipes = Recipe::whereIn('id', $recipeIds)
                    ->with('ingredients.food')
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'total_calories' => round($r->total_calories, 1),
                        'total_protein' => round($r->total_protein, 1),
                        'total_carbs' => round($r->total_carbs, 1),
                        'total_fat' => round($r->total_fat, 1),
                    ]);
            }
        }

        // ID dei pasti completati oggi
        $completedMealIds = [];
        if ($plan) {
            $completedMealIds = MealCompletion::where('client_id', $clientProfile->id)
                ->where('date', today())
                ->pluck('plan_meal_id')
                ->toArray();
        }

        // Percentuale adesione ultimi 7 giorni
        $adherence = null;
        if ($plan) {
            $mealCount = $plan->meals()->count();
            if ($mealCount > 0) {
                $totalExpected = $mealCount * 7;
                $totalCompleted = MealCompletion::where('client_id', $clientProfile->id)
                    ->where('date', '>=', today()->subDays(6))
                    ->count();
                $adherence = $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100) : 0;
            }
        }

        return Inertia::render('Client/Plan', [
            'plan' => $plan,
            'recipes' => $recipes,
            'mealTypes' => collect(MealType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
            'completedMealIds' => $completedMealIds,
            'adherence' => $adherence,
            'today' => today()->toDateString(),
        ]);
    }
}
