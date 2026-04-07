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
        $foods = Food::forNutritionist($request->user()->id)
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
            'notes' => 'nullable|string',
        ]);

        $food->update($validated);

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento aggiornato.');
    }

    public function destroy(Food $food)
    {
        $this->authorizeFood($food);
        $food->delete();

        return redirect()->route('nutritionist.foods.index')
            ->with('success', 'Alimento eliminato.');
    }

    private function authorizeFood(Food $food): void
    {
        abort_unless(
            $food->nutritionist_id === null || $food->nutritionist_id === auth()->id(),
            403
        );
    }
}
