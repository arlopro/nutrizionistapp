<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || !SubscriptionService::hasFeature($user, $feature)) {
            $plan = SubscriptionService::currentPlan($user ?? $request->user());
            $requiredPlan = match ($feature) {
                'pdf_export', 'custom_pdf_logo' => 'Starter',
                'advanced_tracking', 'advanced_stats' => 'Pro',
                default => 'Starter',
            };

            return redirect()->route('nutritionist.billing')
                ->with('error', "Questa funzionalità è disponibile dal piano {$requiredPlan}. Aggiorna il tuo piano per accedervi.");
        }

        return $next($request);
    }
}
