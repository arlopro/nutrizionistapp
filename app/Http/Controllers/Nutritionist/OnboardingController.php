<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Salva preferenze onboarding (tone + duration) e auto-completa se tutto è fatto.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'client_tone'                    => 'nullable|in:formal,informal,friendly',
            'session_durations'              => 'nullable|array',
            'session_durations.*'            => 'nullable|integer|in:15,20,30,45,60,75,90,120',
            'phone'                          => 'nullable|string|max:20',
        ]);

        $user    = $request->user();
        $profile = $user->nutritionistProfile;

        if (!empty($validated['phone'])) {
            $user->update(['phone' => $validated['phone']]);
        }

        $profileUpdate = [];
        if (array_key_exists('client_tone', $validated) && $validated['client_tone'] !== null) {
            $profileUpdate['client_tone'] = $validated['client_tone'];
        }
        if (array_key_exists('session_durations', $validated) && $validated['session_durations'] !== null) {
            $profileUpdate['session_durations'] = $validated['session_durations'];
        }

        if ($profileUpdate) {
            $profile->update($profileUpdate);
        }

        // Auto-completa se tutti gli step sono completati
        if ($this->allStepsDone($user, $profile->fresh())) {
            $profile->update(['onboarding_completed_at' => now()]);
        }

        return back()->with('success', 'Preferenze salvate.');
    }

    /**
     * Il nutrizionista chiude manualmente l'onboarding (anche se non tutto completato).
     */
    public function dismiss(Request $request)
    {
        $request->user()->nutritionistProfile->update([
            'onboarding_completed_at' => now(),
        ]);

        return back()->with('success', 'Configurazione completata.');
    }

    private function allStepsDone($user, $profile): bool
    {
        return !empty($profile->logo)
            && !empty($profile->business_name)
            && !empty($user->phone)
            && !empty($profile->client_tone)
            && !empty($profile->session_durations)
            && $user->clients()->exists();
    }
}
