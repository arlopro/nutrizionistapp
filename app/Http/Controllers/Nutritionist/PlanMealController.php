<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\NutritionalPlan;
use App\Models\PlanMeal;
use App\Models\PlanMealItem;
use Illuminate\Http\Request;

class PlanMealController extends Controller
{
    public function storeMeal(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'meal_type' => 'required|string',
            'notes' => 'nullable|string',
            'free_text' => 'nullable|string',
        ]);

        $maxSort = $plan->meals()
            ->where('day_of_week', $validated['day_of_week'])
            ->max('sort_order') ?? -1;

        $meal = $plan->meals()->create([
            ...$validated,
            'sort_order' => $maxSort + 1,
        ]);

        $meal->load('items.food', 'items.recipe.ingredients.food');

        return back()->with('success', 'Pasto aggiunto.');
    }

    public function updateMeal(Request $request, NutritionalPlan $plan, PlanMeal $meal)
    {
        $this->authorizePlan($plan);
        abort_unless($meal->nutritional_plan_id === $plan->id, 403);

        $validated = $request->validate([
            'free_text' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $meal->update($validated);

        return back()->with('success', 'Pasto aggiornato.');
    }

    public function destroyMeal(NutritionalPlan $plan, PlanMeal $meal)
    {
        $this->authorizePlan($plan);
        abort_unless($meal->nutritional_plan_id === $plan->id, 403);

        $meal->delete();

        return back()->with('success', 'Pasto eliminato.');
    }

    public function storeItem(Request $request, NutritionalPlan $plan, PlanMeal $meal)
    {
        $this->authorizePlan($plan);
        abort_unless($meal->nutritional_plan_id === $plan->id, 403);

        $validated = $request->validate([
            'food_id' => 'nullable|exists:foods,id',
            'recipe_id' => 'nullable|exists:recipes,id',
            'quantity_grams' => 'nullable|numeric|min:0.1',
            'notes' => 'nullable|string',
        ]);

        $maxSort = $meal->items()->max('sort_order') ?? -1;

        $meal->items()->create([
            ...$validated,
            'sort_order' => $maxSort + 1,
        ]);

        return back()->with('success', 'Alimento aggiunto.');
    }

    public function updateItem(Request $request, NutritionalPlan $plan, PlanMealItem $item)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'quantity_grams' => 'nullable|numeric|min:0.1',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return back()->with('success', 'Elemento aggiornato.');
    }

    public function destroyItem(NutritionalPlan $plan, PlanMealItem $item)
    {
        $this->authorizePlan($plan);

        $item->delete();

        return back()->with('success', 'Elemento rimosso.');
    }

    public function storeAlternative(Request $request, NutritionalPlan $plan, PlanMealItem $item)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'food_id'        => 'nullable|exists:foods,id',
            'recipe_id'      => 'nullable|exists:recipes,id',
            'quantity_grams' => 'nullable|numeric|min:0.1',
        ]);

        $item->alternatives()->create([
            ...$validated,
            'plan_meal_id'   => $item->plan_meal_id,
            'alternative_of' => $item->id,
            'sort_order'     => $item->alternatives()->count(),
        ]);

        return back()->with('success', 'Alternativa aggiunta.');
    }

    /**
     * Duplica tutti i pasti di un giorno su un altro giorno (elimina prima i pasti esistenti nel target).
     */
    public function duplicateDay(Request $request, NutritionalPlan $plan, int $day)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'target_day' => 'required|integer|min:0|max:6|different:day',
        ]);

        $targetDay = $validated['target_day'];

        // Elimina i pasti già presenti nel giorno target
        $plan->meals()->where('day_of_week', $targetDay)->delete();

        // Copia i pasti dal giorno sorgente
        $sourceMeals = $plan->meals()
            ->where('day_of_week', $day)
            ->with('items')
            ->get();

        foreach ($sourceMeals as $meal) {
            $newMeal = $plan->meals()->create([
                'day_of_week' => $targetDay,
                'meal_type' => $meal->meal_type,
                'sort_order' => $meal->sort_order,
                'notes' => $meal->notes,
                'free_text' => $meal->free_text,
            ]);

            foreach ($meal->items as $item) {
                $newMeal->items()->create([
                    'food_id' => $item->food_id,
                    'recipe_id' => $item->recipe_id,
                    'quantity_grams' => $item->quantity_grams,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ]);
            }
        }

        return back()->with('success', 'Giorno duplicato.');
    }

    /**
     * Applica i pasti di un giorno a tutti gli altri 6 giorni della settimana.
     */
    public function applyDayToWeek(NutritionalPlan $plan, int $day)
    {
        $this->authorizePlan($plan);

        $sourceMeals = $plan->meals()
            ->where('day_of_week', $day)
            ->with('items')
            ->get();

        $otherDays = array_filter(range(0, 6), fn ($d) => $d !== $day);

        foreach ($otherDays as $targetDay) {
            $plan->meals()->where('day_of_week', $targetDay)->delete();

            foreach ($sourceMeals as $meal) {
                $newMeal = $plan->meals()->create([
                    'day_of_week' => $targetDay,
                    'meal_type' => $meal->meal_type,
                    'sort_order' => $meal->sort_order,
                    'notes' => $meal->notes,
                    'free_text' => $meal->free_text,
                ]);

                foreach ($meal->items as $item) {
                    $newMeal->items()->create([
                        'food_id' => $item->food_id,
                        'recipe_id' => $item->recipe_id,
                        'quantity_grams' => $item->quantity_grams,
                        'notes' => $item->notes,
                        'sort_order' => $item->sort_order,
                    ]);
                }
            }
        }

        return back()->with('success', 'Giorno applicato a tutta la settimana.');
    }

    private function authorizePlan(NutritionalPlan $plan): void
    {
        abort_unless($plan->nutritionist_id === auth()->id(), 403);
    }
}
