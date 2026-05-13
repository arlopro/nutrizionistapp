<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\FoodCategory;
use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        $foods = Food::visibleTo($request->user()->id)
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category', $category);
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Nutritionist/Foods/Index', [
            'foods' => $foods,
            'filters' => $request->only(['search', 'category']),
            'categories' => collect(FoodCategory::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Nutritionist/Foods/Create', [
            'categories' => collect(FoodCategory::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'calories_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
            'fiber_per_100g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0',
            'potassium_mg' => 'nullable|numeric|min:0',
            'calcium_mg' => 'nullable|numeric|min:0',
            'iron_mg' => 'nullable|numeric|min:0',
            'vitamin_d_mcg' => 'nullable|numeric|min:0',
            'vitamin_b12_mcg' => 'nullable|numeric|min:0',
            'glycemic_index' => 'nullable|integer|min:0|max:200',
            'notes' => 'nullable|string',
        ]);

        Food::create([
            ...$validated,
            'nutritionist_id' => $request->user()->id,
        ]);

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento creato.');
    }

    public function edit(Food $food)
    {
        $this->authorizeFood($food);

        return Inertia::render('Nutritionist/Foods/Edit', [
            'food' => $food,
            'categories' => collect(FoodCategory::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()]),
        ]);
    }

    public function update(Request $request, Food $food)
    {
        $this->authorizeFood($food);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'calories_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
            'fiber_per_100g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0',
            'potassium_mg' => 'nullable|numeric|min:0',
            'calcium_mg' => 'nullable|numeric|min:0',
            'iron_mg' => 'nullable|numeric|min:0',
            'vitamin_d_mcg' => 'nullable|numeric|min:0',
            'vitamin_b12_mcg' => 'nullable|numeric|min:0',
            'glycemic_index' => 'nullable|integer|min:0|max:200',
            'notes' => 'nullable|string',
        ]);

        $food->update($validated);

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento aggiornato.');
    }

    public function destroy(Food $food)
    {
        $this->authorizeFood($food);

        $nutritionistId = request()->user()->id;
        if ($food->isUsedByNutritionist($nutritionistId)) {
            return redirect()->route('nutritionist.foods.index')
                ->with('error', 'Impossibile archiviare: questo alimento è utilizzato in uno o più piani nutrizionali.');
        }

        $food->delete();

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento archiviato.');
    }

    public function hide(Food $food)
    {
        $this->authorize('hide', $food);

        $user = request()->user();
        if ($food->isUsedByNutritionist($user->id)) {
            return redirect()->route('nutritionist.foods.index')
                ->with('error', 'Impossibile nascondere: questo alimento è utilizzato in uno o più piani nutrizionali.');
        }

        $food->hiddenByUsers()->syncWithoutDetaching([$user->id => ['created_at' => now()]]);

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento nascosto dalla tua libreria.');
    }

    private function authorizeFood(Food $food): void
    {
        $this->authorize('update', $food);
    }
}
