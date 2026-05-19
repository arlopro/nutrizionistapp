<?php

namespace App\Http\Controllers\Nutritionist;

use App\Enums\ActivityLevel;
use App\Enums\ClientGoal;
use App\Enums\Gender;
use App\Enums\MealType;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Mail\PlanDelivered;
use App\Services\SubscriptionService;
use App\Models\ClientProfile;
use App\Models\Food;
use App\Models\NutritionalPlan;
use App\Models\Recipe;
use App\Services\TdeeCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use setasign\Fpdi\Fpdi;
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
            ->get(['client_profiles.id', 'user_id', 'date_of_birth', 'gender', 'height_cm', 'initial_weight_kg', 'activity_level', 'goal']);

        $templates = NutritionalPlan::where('nutritionist_id', $request->user()->id)
            ->templates()
            ->orderBy('template_name')
            ->get(['id', 'template_name', 'title', 'daily_calories', 'protein_grams', 'carbs_grams', 'fat_grams']);

        return Inertia::render('Nutritionist/Plans/Create', [
            'clients' => $clients,
            'statuses' => collect(PlanStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'templates' => $templates,
            'activityLevels' => collect(ActivityLevel::cases())->map(fn ($a) => ['value' => $a->value, 'label' => $a->label()]),
            'goals' => collect(ClientGoal::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
            'genders' => collect(Gender::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()]),
        ]);
    }

    public function calculateTdee(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:client_profiles,id',
            'weight_kg' => 'required|numeric|min:20|max:400',
            'height_cm' => 'required|numeric|min:50|max:300',
            'age' => 'required|integer|min:1|max:150',
            'gender' => 'required|string',
            'activity_level' => 'required|string',
            'goal' => 'nullable|string',
            'formula' => 'nullable|string|in:mifflin,harris_benedict',
        ]);

        if (!empty($validated['client_id'])) {
            $client = ClientProfile::findOrFail($validated['client_id']);
            $this->authorize('view', $client);
        }

        return response()->json(TdeeCalculator::calculate(
            weightKg: (float) $validated['weight_kg'],
            heightCm: (float) $validated['height_cm'],
            age: (int) $validated['age'],
            gender: Gender::from($validated['gender']),
            activityLevel: ActivityLevel::from($validated['activity_level']),
            goal: !empty($validated['goal']) ? ClientGoal::from($validated['goal']) : null,
            formula: $validated['formula'] ?? 'mifflin',
        ));
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
        $this->authorize('view', $client);

        $plan = NutritionalPlan::create([
            ...collect($validated)->except('template_id')->toArray(),
            'nutritionist_id' => $request->user()->id,
        ]);

        // Applica template se selezionato
        if (!empty($validated['template_id'])) {
            $template = NutritionalPlan::where('id', $validated['template_id'])
                ->where('nutritionist_id', $request->user()->id)
                ->where('is_template', true)
                ->with(['meals.items', 'supplements'])
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

            $this->copySupplements($template, $plan);
        }

        $this->sendPlanDeliveredEmail($plan, $client);

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
            'supplements',
        ]);

        $foods = Food::forNutritionist($request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g', 'fiber_per_100g', 'sodium_mg', 'potassium_mg', 'calcium_mg', 'iron_mg', 'vitamin_d_mcg', 'vitamin_b12_mcg', 'glycemic_index']);

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

        $oldStatus = $plan->status;
        $plan->update($validated);

        if ($oldStatus !== 'active' && $validated['status'] === 'active') {
            $client = $plan->client;
            if ($client) {
                $this->sendPlanDeliveredEmail($plan, $client);
            }
        }

        return redirect()->route('nutritionist.plans.show', $plan)
            ->with('success', 'Piano aggiornato.');
    }

    public function duplicate(NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        $plan->load(['meals.items', 'supplements']);

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
        $this->copySupplements($plan, $newPlan);

        return redirect()->route('nutritionist.plans.show', $newPlan)
            ->with('success', 'Piano duplicato.');
    }

    /**
     * Salva il piano come template.
     */
    public function saveAsTemplate(Request $request, NutritionalPlan $plan)
    {
        $this->authorizePlan($plan);

        if (!SubscriptionService::canCreateTemplate($request->user())) {
            $limit = SubscriptionService::featureLimit($request->user(), 'plan_template_limit');
            return back()->with('error', "Hai raggiunto il limite di {$limit} template del piano Free. Passa al piano Starter per template illimitati.");
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        $plan->load(['meals.items', 'supplements']);

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
        $this->copySupplements($plan, $template);

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

        $plan->load(['client.user', 'meals' => fn ($q) => $q->orderBy('day_of_week')->orderBy('sort_order'), 'meals.items.food', 'meals.items.recipe.ingredients.food', 'supplements']);

        $nutritionist = auth()->user();
        $profile = $nutritionist->nutritionistProfile;

        $logoBase64 = null;
        if ($profile?->logo && Storage::disk('public')->exists($profile->logo) && SubscriptionService::hasFeature($nutritionist, 'custom_pdf_logo')) {
            $logoContent = Storage::disk('public')->get($profile->logo);
            $mimeType = Storage::disk('public')->mimeType($profile->logo);
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        }

        $appLogoPath = public_path('images/logo-nutrizionistapp.png');
        $appLogoBase64 = file_exists($appLogoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($appLogoPath))
            : null;

        $sharedData = [
            'plan' => $plan,
            'nutritionist' => $nutritionist,
            'profile' => $profile,
            'logoBase64' => $logoBase64,
            'appLogoBase64' => $appLogoBase64,
        ];

        // Portrait PDF (main document)
        $portraitOutput = Pdf::loadView('pdf.plan', $sharedData)
            ->setPaper('a4', 'portrait')
            ->setOption('isCompressOutput', false)
            ->output();

        // Landscape PDF (weekly recap)
        $landscapeOutput = Pdf::loadView('pdf.plan_weekly', $sharedData)
            ->setPaper('a4', 'landscape')
            ->setOption('isCompressOutput', false)
            ->output();

        // Merge using FPDI
        $merger = new Fpdi();

        $portraitStream = $this->pdfStringToStream($portraitOutput);
        $portraitPages = $merger->setSourceFile($portraitStream);
        for ($i = 1; $i <= $portraitPages; $i++) {
            $tpl = $merger->importPage($i);
            $size = $merger->getTemplateSize($tpl);
            $merger->AddPage(
                $size['width'] > $size['height'] ? 'L' : 'P',
                [$size['width'], $size['height']]
            );
            $merger->useTemplate($tpl);
        }

        $landscapeStream = $this->pdfStringToStream($landscapeOutput);
        $landscapePages = $merger->setSourceFile($landscapeStream);
        for ($i = 1; $i <= $landscapePages; $i++) {
            $tpl = $merger->importPage($i);
            $size = $merger->getTemplateSize($tpl);
            $merger->AddPage(
                $size['width'] > $size['height'] ? 'L' : 'P',
                [$size['width'], $size['height']]
            );
            $merger->useTemplate($tpl);
        }

        $merged = $merger->Output('', 'S');
        $filename = Str::slug($plan->title ?: 'piano-nutrizionale') . '.pdf';

        return response($merged, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function pdfStringToStream(string $content): \setasign\Fpdi\PdfParser\StreamReader
    {
        return \setasign\Fpdi\PdfParser\StreamReader::createByString($content);
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

    private function copySupplements(NutritionalPlan $source, NutritionalPlan $dest): void
    {
        foreach ($source->supplements as $supplement) {
            $dest->supplements()->create([
                'name' => $supplement->name,
                'dosage' => $supplement->dosage,
                'dosage_unit' => $supplement->dosage_unit,
                'timing' => $supplement->timing,
                'duration' => $supplement->duration,
                'notes' => $supplement->notes,
                'sort_order' => $supplement->sort_order,
            ]);
        }
    }

    private function sendPlanDeliveredEmail(NutritionalPlan $plan, ClientProfile $client): void
    {
        $nutritionist = $plan->nutritionist ?? auth()->user();
        $profile = $nutritionist?->nutritionistProfile;
        $settings = $profile ? $profile->mergedNotificationSettings() : ['plan_delivered' => true];

        if (! $settings['plan_delivered']) {
            return;
        }

        $clientEmail = $client->user?->email;
        if ($clientEmail) {
            $plan->loadMissing('client.user', 'client.nutritionist');
            Mail::to($clientEmail)->queue(new PlanDelivered($plan));
        }
    }

    private function authorizePlan(NutritionalPlan $plan): void
    {
        $this->authorize('view', $plan);
    }
}
