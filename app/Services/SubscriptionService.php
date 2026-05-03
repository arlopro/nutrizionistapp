<?php

namespace App\Services;

use App\Models\GiftedPlan;
use App\Models\PaypalSubscription;
use App\Models\User;

class SubscriptionService
{
    const FEATURES = [
        'free' => [
            'client_limit'        => 5,
            'plan_template_limit' => 2,
            'pdf_export'          => false,
            'advanced_tracking'   => false,
            'advanced_stats'      => false,
            'custom_pdf_logo'     => false,
        ],
        'starter' => [
            'client_limit'        => 20,
            'plan_template_limit' => null,
            'pdf_export'          => true,
            'advanced_tracking'   => false,
            'advanced_stats'      => false,
            'custom_pdf_logo'     => true,
        ],
        'pro' => [
            'client_limit'        => 100,
            'plan_template_limit' => null,
            'pdf_export'          => true,
            'advanced_tracking'   => true,
            'advanced_stats'      => true,
            'custom_pdf_logo'     => true,
        ],
        'business' => [
            'client_limit'        => null,
            'plan_template_limit' => null,
            'pdf_export'          => true,
            'advanced_tracking'   => true,
            'advanced_stats'      => true,
            'custom_pdf_logo'     => true,
        ],
    ];

    public static function hasFeature(User $user, string $key): bool
    {
        $plan = self::currentPlan($user);
        return (bool) (self::FEATURES[$plan][$key] ?? false);
    }

    public static function featureLimit(User $user, string $key): ?int
    {
        $plan = self::currentPlan($user);
        return self::FEATURES[$plan][$key] ?? null;
    }

    public static function canCreateTemplate(User $user): bool
    {
        $limit = self::featureLimit($user, 'plan_template_limit');
        if ($limit === null) return true;

        $count = $user->plans()->where('is_template', true)->count();
        return $count < $limit;
    }

    /**
     * Definizione dei piani con limiti e prezzi Stripe.
     */
    public static function plans(): array
    {
        return [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'client_limit' => 5,
                'stripe_price_id' => null,
                'features' => [
                    'Fino a 5 clienti attivi',
                    'Piani nutrizionali',
                    'Check-in clienti',
                    'Appuntamenti',
                ],
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => 12,
                'client_limit' => 20,
                'stripe_price_id' => config('cashier.price_starter'),
                'features' => [
                    'Fino a 20 clienti attivi',
                    'Tutte le funzionalità core',
                    'Template piani illimitati',
                    'Export PDF',
                    'Supporto email',
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 24,
                'client_limit' => 100,
                'stripe_price_id' => config('cashier.price_pro'),
                'features' => [
                    'Fino a 100 clienti attivi',
                    'Tutto di Starter',
                    'Tracking avanzato',
                    'Statistiche',
                    'Supporto prioritario',
                ],
            ],
            'business' => [
                'name' => 'Business',
                'price' => 49,
                'client_limit' => null, // illimitati
                'stripe_price_id' => config('cashier.price_business'),
                'features' => [
                    'Clienti illimitati',
                    'Tutto di Pro',
                    'Multi-nutrizionista (coming soon)',
                    'Statistiche avanzate (coming soon)',
                    'Supporto dedicato',
                ],
            ],
        ];
    }

    /**
     * Restituisce il piano corrente dell'utente (gift > Stripe > PayPal > free).
     */
    public static function currentPlan(User $user): string
    {
        $gift = GiftedPlan::where('user_id', $user->id)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('created_at')
            ->first();

        if ($gift) {
            return $gift->plan_key;
        }

        if ($user->subscribed('default')) {
            foreach (['business', 'pro', 'starter'] as $plan) {
                $priceId = config("cashier.price_{$plan}");
                if ($priceId && $user->subscribedToPrice($priceId, 'default')) {
                    return $plan;
                }
            }
        }

        $paypalSub = PaypalSubscription::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        if ($paypalSub) {
            return $paypalSub->plan_key;
        }

        return 'free';
    }

    /**
     * Restituisce il limite clienti attivi per il piano corrente.
     */
    public static function clientLimit(User $user): ?int
    {
        $plan = self::currentPlan($user);
        return self::plans()[$plan]['client_limit'];
    }

    /**
     * Verifica se il nutrizionista può aggiungere un altro cliente attivo.
     */
    public static function canAddClient(User $user): bool
    {
        $limit = self::clientLimit($user);
        if ($limit === null) return true; // illimitati

        $activeCount = $user->clients()->active()->count();
        return $activeCount < $limit;
    }

    /**
     * Restituisce i dati del piano corrente.
     */
    public static function currentPlanData(User $user): array
    {
        $planKey = self::currentPlan($user);
        $plan = self::plans()[$planKey];
        $activeCount = $user->clients()->active()->count();

        return [
            'key' => $planKey,
            'name' => $plan['name'],
            'price' => $plan['price'],
            'client_limit' => $plan['client_limit'],
            'active_clients' => $activeCount,
            'can_add_client' => self::canAddClient($user),
            'subscription' => $user->subscription('default'),
        ];
    }
}
