<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MealCompletion;
use App\Models\PlanMeal;
use Illuminate\Http\Request;

class MealCompletionController extends Controller
{
    public function toggle(Request $request, PlanMeal $meal)
    {
        $clientProfile = $request->user()->clientProfile;
        abort_unless($clientProfile, 403);

        // Verifica che il pasto appartenga al piano attivo del cliente
        $activePlan = $clientProfile->activePlan();
        abort_unless($activePlan && $meal->nutritional_plan_id === $activePlan->id, 403);

        $date = today()->toDateString();

        $existing = MealCompletion::where([
            'plan_meal_id' => $meal->id,
            'client_id'    => $clientProfile->id,
            'date'         => $date,
        ])->first();

        if ($existing) {
            $existing->delete();
            $completed = false;
        } else {
            MealCompletion::create([
                'plan_meal_id' => $meal->id,
                'client_id'    => $clientProfile->id,
                'date'         => $date,
            ]);
            $completed = true;
        }

        return back()->with('success', $completed ? 'Pasto completato!' : 'Pasto rimosso dai completati.');
    }
}
