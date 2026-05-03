<?php

namespace App\Http\Controllers\Nutritionist;

use App\Http\Controllers\Controller;
use App\Services\PaymentCredentials;
use App\Services\PayPalSubscriptionService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->nutritionistProfile;

        $planData   = SubscriptionService::currentPlanData($user);
        $plans      = SubscriptionService::plans();
        $invoices   = $this->getInvoices($user);
        $nextBilling = $this->getNextBillingDate($user);

        return Inertia::render('Nutritionist/Billing', [
            'planData'         => $planData,
            'plans'            => $plans,
            'paymentProviders' => [
                'stripe' => PaymentCredentials::stripeConfigured(),
                'paypal' => PaymentCredentials::paypalConfigured(),
            ],
            'invoices'         => $invoices,
            'nextBillingDate'  => $nextBilling,
            'billingInfo'      => $profile?->billing_info ?? [],
        ]);
    }

    public function updateBillingInfo(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'tax_id'        => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'country'       => 'nullable|string|size:2',
            'sdi_code'      => 'nullable|string|max:20',
            'pec'           => 'nullable|email|max:255',
        ]);

        $user    = $request->user();
        $profile = $user->nutritionistProfile;

        $profile->update(['billing_info' => $data]);

        // Sync to Stripe if customer exists
        if ($user->stripe_id && PaymentCredentials::stripeConfigured()) {
            try {
                $user->updateStripeCustomer([
                    'name'    => $data['business_name'] ?: $user->name . ' ' . $user->last_name,
                    'address' => [
                        'line1'       => $data['address_line1'] ?? '',
                        'city'        => $data['city'] ?? '',
                        'postal_code' => $data['postal_code'] ?? '',
                        'country'     => $data['country'] ?? 'IT',
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to sync billing info to Stripe: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Dati di fatturazione aggiornati.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan'     => 'required|in:starter,pro,business',
            'provider' => 'required|in:stripe,paypal',
        ]);

        $plan     = $request->plan;
        $provider = $request->provider;

        if ($provider === 'paypal') {
            abort_unless(PaymentCredentials::paypalConfigured(), 422, 'PayPal non configurato.');

            $approvalUrl = app(PayPalSubscriptionService::class)->createSubscription(
                $request->user(),
                $plan,
                route('nutritionist.billing') . '?success=1&provider=paypal',
                route('nutritionist.billing') . '?cancelled=1'
            );

            return Inertia::location($approvalUrl);
        }

        // Stripe
        abort_unless(PaymentCredentials::stripeConfigured(), 422, 'Stripe non configurato.');

        $priceId = config("cashier.price_{$plan}");
        abort_if(!$priceId, 422, 'Piano non disponibile.');

        // Ensure Stripe customer exists and has billing info
        $user = $request->user();
        $this->ensureStripeCustomerHasBillingInfo($user);

        $checkout = $user
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('nutritionist.billing.stripe-success') . '?session_id={CHECKOUT_SESSION_ID}&plan=' . $plan,
                'cancel_url'  => route('nutritionist.billing') . '?cancelled=1',
            ]);

        return Inertia::location($checkout->url);
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        $user      = $request->user();

        if ($sessionId) {
            try {
                $stripe  = new \Stripe\StripeClient(config('cashier.secret'));
                $session = $stripe->checkout->sessions->retrieve($sessionId, [
                    'expand' => ['subscription'],
                ]);

                $subscription = $session->subscription;

                if ($subscription && $subscription->id) {
                    $sub = $user->subscriptions()->updateOrCreate(
                        ['stripe_id' => $subscription->id],
                        [
                            'type'          => 'default',
                            'stripe_status' => $subscription->status,
                            'stripe_price'  => $subscription->items->data[0]->price->id ?? null,
                            'quantity'      => $subscription->items->data[0]->quantity ?? 1,
                            'trial_ends_at' => $subscription->trial_end
                                ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end)
                                : null,
                            'ends_at'       => null,
                        ]
                    );

                    foreach ($subscription->items->data as $item) {
                        $sub->items()->updateOrCreate(
                            ['stripe_id' => $item->id],
                            [
                                'stripe_product' => $item->price->product,
                                'stripe_price'   => $item->price->id,
                                'quantity'       => $item->quantity ?? 1,
                            ]
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe success sync failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('nutritionist.billing')
            ->with('success', 'Abbonamento attivato con successo! Benvenuto nel nuovo piano.');
    }

    public function portal(Request $request)
    {
        $url = $request->user()->billingPortalUrl(route('nutritionist.billing'));
        return Inertia::location($url);
    }

    private function getInvoices(\App\Models\User $user): array
    {
        if (!$user->stripe_id || !PaymentCredentials::stripeConfigured()) {
            return [];
        }

        try {
            return collect($user->invoices())
                ->map(fn ($inv) => [
                    'id'          => $inv->id,
                    'number'      => $inv->number,
                    'date'        => $inv->date()?->toIso8601String(),
                    'total'       => $inv->total(),
                    'status'      => $inv->status,
                    'pdf_url'     => $inv->invoice_pdf,
                    'hosted_url'  => $inv->hosted_invoice_url,
                ])
                ->values()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getNextBillingDate(\App\Models\User $user): ?string
    {
        if (!$user->stripe_id || !PaymentCredentials::stripeConfigured()) {
            return null;
        }

        try {
            $sub = $user->subscription('default');
            if (!$sub || !in_array($sub->stripe_status, ['active', 'trialing'])) {
                return null;
            }

            $stripeSub = $sub->asStripeSubscription();
            return $stripeSub->current_period_end
                ? \Carbon\Carbon::createFromTimestamp($stripeSub->current_period_end)->toIso8601String()
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function ensureStripeCustomerHasBillingInfo(\App\Models\User $user): void
    {
        if (!$user->stripe_id) {
            $user->createOrGetStripeCustomer();
        }

        $billing = $user->nutritionistProfile?->billing_info;
        if (!empty($billing)) {
            try {
                $user->updateStripeCustomer([
                    'name'    => $billing['business_name'] ?: $user->name . ' ' . $user->last_name,
                    'address' => [
                        'line1'       => $billing['address_line1'] ?? '',
                        'city'        => $billing['city'] ?? '',
                        'postal_code' => $billing['postal_code'] ?? '',
                        'country'     => $billing['country'] ?? 'IT',
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to sync billing info before checkout: ' . $e->getMessage());
            }
        }
    }
}
