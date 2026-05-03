<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\GiftedPlan;
use App\Models\User;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function store(Request $request, User $user)
    {
        abort_unless($user->hasRole('nutritionist'), 404);

        $data = $request->validate([
            'plan_key' => 'required|in:starter,pro,business',
            'months'   => 'required|in:1,3,6,12,unlimited',
            'note'     => 'nullable|string|max:500',
        ]);

        $expiresAt = $data['months'] === 'unlimited'
            ? null
            : now()->addMonths((int) $data['months']);

        GiftedPlan::create([
            'user_id'    => $user->id,
            'granted_by' => $request->user()->id,
            'plan_key'   => $data['plan_key'],
            'expires_at' => $expiresAt,
            'note'       => $data['note'] ?? null,
        ]);

        return back()->with('success', "Piano {$data['plan_key']} regalato a {$user->name} " . ($expiresAt ? "fino al {$expiresAt->format('d/m/Y')}" : "a tempo indeterminato") . '.');
    }

    public function destroy(Request $request, User $user, GiftedPlan $gift)
    {
        abort_unless($gift->user_id === $user->id, 404);

        $gift->delete();

        return back()->with('success', 'Regalo rimosso.');
    }
}
