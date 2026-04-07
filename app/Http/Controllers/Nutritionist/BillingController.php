<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $planData = SubscriptionService::currentPlanData($user);
        $plans = SubscriptionService::plans();

        return Inertia::render('Nutritionist/Billing', [
            'planData' => $planData,
            'plans' => $plans,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate(['plan' => 'required|in:starter,pro,business']);

        $plan = $request->plan;
        $priceId = config("cashier.price_{$plan}");

        abort_if(!$priceId, 422, 'Piano non disponibile.');

        $checkout = $request->user()
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('nutritionist.billing') . '?success=1',
                'cancel_url' => route('nutritionist.billing') . '?cancelled=1',
            ]);

        return Inertia::location($checkout->url);
    }

    public function portal(Request $request)
    {
        $url = $request->user()->billingPortalUrl(route('nutritionist.billing'));
        return Inertia::location($url);
    }
}
