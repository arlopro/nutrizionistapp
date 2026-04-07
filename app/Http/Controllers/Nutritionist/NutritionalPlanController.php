<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\MealType;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Food;
use App\Models\NutritionalPlan;
use App\Models\Recipe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NutritionalPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = NutritionalPlan::where('nutritionist_id', $request->user()->id)
            ->plans()
            ->with('client.user')
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'status', 'client_id']),
            'statuses' => collect(PlanStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'clients' => $clients,
        ]);
    }

    public function create(Request $request)
    {
        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        $templates = NutritionalPlan::where('nutritionist_id', $request->user()->id)
            ->templates()
            ->orderBy('template_name')
            ->get(['id', 'template_name', 'title', 'daily_calories', 'protein_grams', 'carbs_grams', 'fat_grams']);

        return Inertia::render('Nutritionist/Plans/Create', [
            'clients' => $clients,
            'statuses' => collect(PlanStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:client_profiles,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'daily_calories' => 'nullable|integer|min:0',
            'protein_grams' => 'nullable|numeric|min:0',
            'carbs_grams' => 'nullable|numeric|min:0',
            'fat_grams' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'template_id' => 'nullable|exists:nutritional_plans,id',
        ]);

        $client = ClientProfile::findOrFail($validated['client_id']);
        abort_unless($client->nutritionist_id === $request->user()->id, 403);

        $plan = NutritionalPlan::create([
            ...collect($validated)->except('template_id')->toArray(),
            'nutritionist_id' => $request->user()->id,
        ]);

        // Applica template se selezionato
        if (!empty($validated['template_id'])) {
            $template = NutritionalPlan::where('id', $validated['template_id'])
                ->where('nutritionist_id', $request->user()->id)
                ->where('is_template', true)
                ->with('meals.items')
                ->firstOrFail();

            foreach ($template->meals as $meal) {
                $newMeal = $plan->meals()->create([
                    'day_of_week' => $meal->day_of_week,
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

        return redirect()->route('nutritionist.plans.show', $plan)
            ->with('success', 'Piano creato. Ora puoi aggiungere i pasti.');
    }

    public function show(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $plan->load([
            'client.user',
            'meals.items.food',
            'meals.items.recipe.ingredients.food',
            'meals.items.alternatives.food',
            'meals.items.alternatives.recipe',
        ]);

        $foods = Food::forNutritionist($request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g']);

        $recipes = Recipe::where('nutritionist_id', $request->user()->id)
            ->with('ingredients.food')
            ->orderBy('name')
            ->get();

        $recipesData = $recipes->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'total_calories' => round($r->total_calories, 1),
            'total_protein' => round($r->total_protein, 1),
            'total_carbs' => round($r->total_carbs, 1),
            'total_fat' => round($r->total_fat, 1),
        ]);

        return Inertia::render('Nutritionist/Plans/Show', [
            'plan' => $plan,
            'foods' => $foods,
            'recipes' => $recipesData,
            'mealTypes' => collect(MealType::cases())->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()]),
            'statuses' => collect(PlanStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function edit(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $clients = $request->user()->clients()
            ->with('user:id,name')
            ->get(['client_profiles.id', 'user_id']);

        return Inertia::render('Nutritionist/Plans/Edit', [
            'plan' => $plan,
            'clients' => $clients,
            'statuses' => collect(PlanStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function update(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'daily_calories' => 'nullable|integer|min:0',
            'protein_grams' => 'nullable|numeric|min:0',
            'carbs_grams' => 'nullable|numeric|min:0',
            'fat_grams' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $plan->update($validated);

        return redirect()->route('nutritionist.plans.show', $plan)
            ->with('success', 'Piano aggiornato.');
    }

    public function duplicate(NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $plan->load('meals.items');

        $newPlan = NutritionalPlan::create([
            'client_id' => $plan->client_id,
            'nutritionist_id' => $plan->nutritionist_id,
            'title' => $plan->title . ' (copia)',
            'description' => $plan->description,
            'start_date' => now()->toDateString(),
            'end_date' => $plan->end_date,
            'daily_calories' => $plan->daily_calories,
            'protein_grams' => $plan->protein_grams,
            'carbs_grams' => $plan->carbs_grams,
            'fat_grams' => $plan->fat_grams,
            'status' => 'draft',
            'notes' => $plan->notes,
        ]);

        $this->copyMeals($plan, $newPlan);

        return redirect()->route('nutritionist.plans.show', $newPlan)
            ->with('success', 'Piano duplicato.');
    }

    /**
     * Salva il piano come template.
     */
    public function saveAsTemplate(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        $plan->load('meals.items');

        $template = NutritionalPlan::create([
            'nutritionist_id' => $plan->nutritionist_id,
            'client_id' => null,
            'title' => $plan->title,
            'description' => $plan->description,
            'start_date' => now()->toDateString(),
            'daily_calories' => $plan->daily_calories,
            'protein_grams' => $plan->protein_grams,
            'carbs_grams' => $plan->carbs_grams,
            'fat_grams' => $plan->fat_grams,
            'status' => 'draft',
            'notes' => $plan->notes,
            'is_template' => true,
            'template_name' => $validated['template_name'],
        ]);

        $this->copyMeals($plan, $template);

        return back()->with('success', "Template \"{$validated['template_name']}\" salvato.");
    }

    /**
     * Lista template del nutrizionista.
     */
    public function templates(Request $request)
    {
        $templates = NutritionalPlan::where('nutritionist_id', $request->user()->id)
            ->templates()
            ->withCount('meals')
            ->orderBy('template_name')
            ->get();

        return Inertia::render('Nutritionist/Plans/Templates', [
            'templates' => $templates,
        ]);
    }

    /**
     * Elimina template.
     */
    public function destroyTemplate(NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);
        abort_unless($plan->is_template, 404);
        $plan->delete();

        return redirect()->route('nutritionist.plans.templates')
            ->with('success', 'Template eliminato.');
    }

    public function exportPdf(NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $plan->load(['client.user', 'meals' => fn ($q) => $q->orderBy('day_of_week')->orderBy('sort_order'), 'meals.items.food', 'meals.items.recipe.ingredients.food']);

        $nutritionist = auth()->user();
        $profile = $nutritionist->nutritionistProfile;

        $logoBase64 = null;
        if ($profile?->logo && Storage::disk('public')->exists($profile->logo)) {
            $logoContent = Storage::disk('public')->get($profile->logo);
            $mimeType = Storage::disk('public')->mimeType($profile->logo);
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        }

        $pdf = Pdf::loadView('pdf.plan', [
            'plan' => $plan,
            'nutritionist' => $nutritionist,
            'profile' => $profile,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait');

        $filename = Str::slug($plan->title ?: 'piano-nutrizionale') . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);
        $plan->delete();

        return redirect()->route('nutritionist.plans.index')
            ->with('success', 'Piano eliminato.');
    }

    private function copyMeals(NutritionalPlan $source, NutritionalPlan $dest): void
    {
        foreach ($source->meals as $meal) {
            $newMeal = $dest->meals()->create([
                'day_of_week' => $meal->day_of_week,
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

    private function authorizePlan(NutritionalPlan $plan): void
    {
        abort_unless($plan->nutritionist_id === auth()->id(), 403);
    }
}
