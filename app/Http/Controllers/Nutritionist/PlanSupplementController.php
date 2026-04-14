<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Models\NutritionalPlan;
use App\Models\PlanSupplement;
use Illuminate\Http\Request;

class PlanSupplementController extends Controller
{
    public function store(Request $request, NutritionalPlan $plan)
    {
        $this->authorize('update', $plan);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'dosage_unit' => 'nullable|string|max:30',
            'timing' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $maxOrder = $plan->supplements()->max('sort_order') ?? -1;

        $plan->supplements()->create([
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        return back();
    }

    public function update(Request $request, NutritionalPlan $plan, PlanSupplement $supplement)
    {
        $this->authorize('update', $plan);
        abort_unless($supplement->nutritional_plan_id === $plan->id, 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'dosage_unit' => 'nullable|string|max:30',
            'timing' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $supplement->update($validated);

        return back();
    }

    public function destroy(NutritionalPlan $plan, PlanSupplement $supplement)
    {
        $this->authorize('update', $plan);
        abort_unless($supplement->nutritional_plan_id === $plan->id, 404);

        $supplement->delete();

        return back();
    }
}
