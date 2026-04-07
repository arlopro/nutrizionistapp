<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $recipes = Recipe::where('nutritionist_id', $request->user()->id)
            ->with(['ingredients.food'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($recipe) {
                return [
                    'id' => $recipe->id,
                    'name' => $recipe->name,
                    'description' => $recipe->description,
                    'servings' => $recipe->servings,
                    'prep_time_minutes' => $recipe->prep_time_minutes,
                    'cook_time_minutes' => $recipe->cook_time_minutes,
                    'ingredients_count' => $recipe->ingredients->count(),
                    'total_calories' => round($recipe->total_calories, 1),
                    'total_protein' => round($recipe->total_protein, 1),
                    'total_carbs' => round($recipe->total_carbs, 1),
                    'total_fat' => round($recipe->total_fat, 1),
                ];
            });

        return Inertia::render('Nutritionist/Recipes/Index', [
            'recipes' => $recipes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request)
    {
        $foods = Food::forNutritionist($request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g']);

        return Inertia::render('Nutritionist/Recipes/Create', [
            'foods' => $foods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'servings' => 'required|integer|min:1',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cook_time_minutes' => 'nullable|integer|min:0',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.food_id' => 'required|exists:foods,id',
            'ingredients.*.quantity_grams' => 'required|numeric|min:0.1',
        ]);

        $recipe = Recipe::create([
            'nutritionist_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'servings' => $validated['servings'],
            'prep_time_minutes' => $validated['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $validated['cook_time_minutes'] ?? null,
        ]);

        foreach ($validated['ingredients'] as $index => $ingredient) {
            $recipe->ingredients()->create([
                'food_id' => $ingredient['food_id'],
                'quantity_grams' => $ingredient['quantity_grams'],
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('nutritionist.recipes.index')
            ->with('success', 'Ricetta creata.');
    }

    public function show(Recipe $recipe)
    {
        $this->authorizeRecipe($recipe);

        $recipe->load('ingredients.food');

        return Inertia::render('Nutritionist/Recipes/Show', [
            'recipe' => [
                ...$recipe->toArray(),
                'total_calories' => round($recipe->total_calories, 1),
                'total_protein' => round($recipe->total_protein, 1),
                'total_carbs' => round($recipe->total_carbs, 1),
                'total_fat' => round($recipe->total_fat, 1),
            ],
        ]);
    }

    public function edit(Request $request, Recipe $recipe)
    {
        $this->authorizeRecipe($recipe);

        $recipe->load('ingredients.food');

        $foods = Food::forNutritionist($request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g']);

        return Inertia::render('Nutritionist/Recipes/Edit', [
            'recipe' => [
                ...$recipe->toArray(),
                'total_calories' => round($recipe->total_calories, 1),
                'total_protein' => round($recipe->total_protein, 1),
                'total_carbs' => round($recipe->total_carbs, 1),
                'total_fat' => round($recipe->total_fat, 1),
            ],
            'foods' => $foods,
        ]);
    }

    public function update(Request $request, Recipe $recipe)
    {
        $this->authorizeRecipe($recipe);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'servings' => 'required|integer|min:1',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cook_time_minutes' => 'nullable|integer|min:0',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.food_id' => 'required|exists:foods,id',
            'ingredients.*.quantity_grams' => 'required|numeric|min:0.1',
        ]);

        $recipe->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'servings' => $validated['servings'],
            'prep_time_minutes' => $validated['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $validated['cook_time_minutes'] ?? null,
        ]);

        $recipe->ingredients()->delete();

        foreach ($validated['ingredients'] as $index => $ingredient) {
            $recipe->ingredients()->create([
                'food_id' => $ingredient['food_id'],
                'quantity_grams' => $ingredient['quantity_grams'],
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('nutritionist.recipes.index')
            ->with('success', 'Ricetta aggiornata.');
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorizeRecipe($recipe);
        $recipe->delete();

        return redirect()->route('nutritionist.recipes.index')
            ->with('success', 'Ricetta eliminata.');
    }

    private function authorizeRecipe(Recipe $recipe): void
    {
        $this->authorize('view', $recipe);
    }
}
