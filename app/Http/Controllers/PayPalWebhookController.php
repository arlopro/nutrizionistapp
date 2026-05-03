<?php

namespace App\Http\Controllers;

use App\Models\PaypalSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $eventType = $request->input('event_type');
        $resource  = $request->input('resource', []);

        Log::info("PayPal webhook received: {$eventType}", ['resource_id' => $resource['id'] ?? null]);

        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return response()->json(['status' => 'ignored']);
        }

        $subscription = PaypalSubscription::where('paypal_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return response()->json(['status' => 'not_found']);
        }

        match ($eventType) {
            'BILLING.SUBSCRIPTION.ACTIVATED' => $subscription->update([
                'status'             => 'ACTIVE',
                'current_period_end' => isset($resource['billing_info']['next_billing_time'])
                    ? \Carbon\Carbon::parse($resource['billing_info']['next_billing_time'])
                    : now()->addMonth(),
            ]),
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.EXPIRED'   => $subscription->update(['status' => $eventType === 'BILLING.SUBSCRIPTION.CANCELLED' ? 'CANCELLED' : 'EXPIRED']),
            'BILLING.SUBSCRIPTION.SUSPENDED' => $subscription->update(['status' => 'SUSPENDED']),
            'PAYMENT.SALE.COMPLETED'         => $this->handlePaymentCompleted($resource),
            default                          => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentCompleted(array $resource): void
    {
        $billingAgreementId = $resource['billing_agreement_id'] ?? null;
        if (!$billingAgreementId) return;

        PaypalSubscription::where('paypal_subscription_id', $billingAgreementId)->update([
            'current_period_end' => now()->addMonth(),
        ]);
    }
}
