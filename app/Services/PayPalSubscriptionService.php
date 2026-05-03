<?php

namespace App\Services;

use App\Models\PaypalSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalSubscriptionService
{
    private function client(): PayPalClient
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        return $provider;
    }

    public function createSubscription(User $user, string $planKey, string $returnUrl, string $cancelUrl): string
    {
        $planId = config("services.paypal.plan_{$planKey}");

        if (!$planId) {
            throw new \RuntimeException("PayPal plan ID not configured for plan: {$planKey}");
        }

        $client = $this->client();

        $response = $client->createSubscription([
            'plan_id'             => $planId,
            'start_time'          => now()->addMinutes(1)->toIso8601String(),
            'subscriber'          => [
                'name'            => ['given_name' => $user->name, 'surname' => $user->last_name ?? ''],
                'email_address'   => $user->email,
            ],
            'application_context' => [
                'brand_name'      => config('app.name'),
                'locale'          => 'it-IT',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'     => 'SUBSCRIBE_NOW',
                'payment_method'  => ['payer_selected' => 'PAYPAL', 'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'],
                'return_url'      => $returnUrl,
                'cancel_url'      => $cancelUrl,
            ],
        ]);

        if (empty($response['id'])) {
            Log::error('PayPal createSubscription failed', $response);
            throw new \RuntimeException('PayPal subscription creation failed.');
        }

        $approvalLink = collect($response['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approvalLink) {
            throw new \RuntimeException('PayPal approval link not found.');
        }

        // Store as pending — will be activated by webhook
        PaypalSubscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'paypal_subscription_id' => $response['id'],
                'plan_key'               => $planKey,
                'status'                 => 'APPROVAL_PENDING',
                'current_period_end'     => null,
            ]
        );

        return $approvalLink;
    }

    public function cancel(string $subscriptionId, string $reason = 'User requested cancellation'): bool
    {
        try {
            $client = $this->client();
            $client->cancelSubscription($subscriptionId, $reason);
            PaypalSubscription::where('paypal_subscription_id', $subscriptionId)
                ->update(['status' => 'CANCELLED']);
            return true;
        } catch (\Throwable $e) {
            Log::error('PayPal cancelSubscription failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getStatus(string $subscriptionId): ?array
    {
        try {
            $client = $this->client();
            return $client->showSubscriptionDetails($subscriptionId);
        } catch (\Throwable $e) {
            Log::error('PayPal getStatus failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
