<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\GiftedPlan;
use App\Models\NutritionalPlan;
use App\Models\User;
use App\Services\PaymentCredentials;
use App\Services\SubscriptionService;
use Inertia\Inertia;

class DevController extends Controller
{
    public function dashboard()
    {
        $totalNutritionists = User::role('nutritionist')->count();
        $totalClients       = User::role('client')->count();
        $totalPlans         = NutritionalPlan::where('is_template', false)->count();
        $activePlans        = NutritionalPlan::where('is_template', false)->where('status', 'active')->count();
        $totalAppointments  = Appointment::count();
        $thisMonth          = now()->startOfMonth();

        $newNutritionistsThisMonth = User::role('nutritionist')->where('created_at', '>=', $thisMonth)->count();
        $newClientsThisMonth       = User::role('client')->where('created_at', '>=', $thisMonth)->count();

        // MRR stima (basato su piani attivi per prezzo)
        $planPrices = ['starter' => 12, 'pro' => 24, 'business' => 49];
        $mrr = 0;
        foreach ($planPrices as $planKey => $price) {
            $priceId = config("cashier.price_{$planKey}");
            if ($priceId) {
                $count = \Laravel\Cashier\Subscription::where('stripe_status', 'active')
                    ->whereHas('items', fn ($q) => $q->where('stripe_price', $priceId))
                    ->count();
                $mrr += $count * $price;
            }
        }

        // Top 5 nutrizionisti per numero clienti
        $topNutritionists = User::role('nutritionist')
            ->withCount('clients')
            ->orderByDesc('clients_count')
            ->limit(5)
            ->get(['id', 'name', 'last_name', 'email', 'created_at']);

        // Ultimi 10 piani creati
        $recentPlans = NutritionalPlan::where('is_template', false)
            ->with(['client.user:id,name,last_name', 'nutritionist:id,name,last_name'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dev/Dashboard', [
            'stats' => [
                'totalNutritionists'        => $totalNutritionists,
                'totalClients'              => $totalClients,
                'totalPlans'                => $totalPlans,
                'activePlans'               => $activePlans,
                'totalAppointments'         => $totalAppointments,
                'newNutritionistsThisMonth' => $newNutritionistsThisMonth,
                'newClientsThisMonth'       => $newClientsThisMonth,
                'mrr'                       => $mrr,
            ],
            'topNutritionists' => $topNutritionists,
            'recentPlans'      => $recentPlans,
        ]);
    }

    public function nutritionists()
    {
        $nutritionists = User::role('nutritionist')
            ->withCount([
                'clients',
                'clients as active_clients_count' => fn ($q) => $q->whereHas('nutritionalPlans'),
            ])
            ->with(['nutritionistProfile:id,user_id,specialization,city'])
            ->orderByDesc('created_at')
            ->paginate(30)
            ->through(fn ($u) => [
                'id'                   => $u->id,
                'name'                 => $u->name,
                'last_name'            => $u->last_name,
                'email'                => $u->email,
                'created_at'           => $u->created_at,
                'last_login_at'        => $u->last_login_at,
                'clients_count'        => $u->clients_count,
                'active_clients_count' => $u->active_clients_count,
                'specialization'       => $u->nutritionistProfile?->specialization,
                'city'                 => $u->nutritionistProfile?->city,
                'plans_count'          => NutritionalPlan::where('nutritionist_id', $u->id)->where('is_template', false)->count(),
                'appointments_count'   => Appointment::where('nutritionist_id', $u->id)->count(),
                'plan'                 => SubscriptionService::currentPlan($u),
                'subscription_status'  => $u->subscription('default')?->stripe_status,
                'subscription_ends_at' => $u->subscription('default')?->ends_at,
            ]);

        return Inertia::render('Dev/Nutritionists', [
            'nutritionists' => $nutritionists,
        ]);
    }

    public function showNutritionist(User $user)
    {
        abort_unless($user->hasRole('nutritionist'), 404);

        $user->load([
            'nutritionistProfile',
            'clients.user:id,name,last_name,email',
            'clients.nutritionalPlans',
        ]);

        $plans = NutritionalPlan::where('nutritionist_id', $user->id)
            ->where('is_template', false)
            ->with('client.user:id,name,last_name')
            ->withCount('meals')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $appointments = Appointment::where('nutritionist_id', $user->id)
            ->with('client.user:id,name,last_name')
            ->orderByDesc('starts_at')
            ->limit(20)
            ->get();

        $recentActivity = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->orWhere('subject_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Payment stats from Stripe
        $invoiceStats = ['count' => 0, 'total_cents' => 0];
        if ($user->stripe_id && PaymentCredentials::stripeConfigured()) {
            try {
                $invoices = $user->invoices();
                $invoiceStats = [
                    'count'       => count($invoices),
                    'total_cents' => collect($invoices)->sum(fn ($inv) => $inv->rawTotal()),
                ];
            } catch (\Throwable) {}
        }

        // Gifted plans
        $giftedPlans = GiftedPlan::where('user_id', $user->id)
            ->with('grantedBy:id,name,last_name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($g) => [
                'id'         => $g->id,
                'plan_key'   => $g->plan_key,
                'expires_at' => $g->expires_at?->toIso8601String(),
                'is_active'  => $g->isActive(),
                'note'       => $g->note,
                'granted_by' => $g->grantedBy?->name . ' ' . $g->grantedBy?->last_name,
                'created_at' => $g->created_at->toIso8601String(),
            ]);

        return Inertia::render('Dev/NutritionistShow', [
            'nutritionist'   => array_merge($user->toArray(), [
                'plan'                 => SubscriptionService::currentPlan($user),
                'subscription_status'  => $user->subscription('default')?->stripe_status,
                'subscription_ends_at' => $user->subscription('default')?->ends_at,
                'trial_ends_at'        => $user->trial_ends_at,
                'clients_count'        => $user->clients->count(),
                'invoice_count'        => $invoiceStats['count'],
                'total_paid_cents'     => $invoiceStats['total_cents'],
            ]),
            'plans'          => $plans,
            'appointments'   => $appointments,
            'recentActivity' => $recentActivity,
            'giftedPlans'    => $giftedPlans,
        ]);
    }

    public function plans()
    {
        $plans = NutritionalPlan::where('is_template', false)
            ->with(['client.user:id,name,last_name', 'nutritionist:id,name,last_name'])
            ->withCount('meals')
            ->orderByDesc('created_at')
            ->paginate(40);

        return Inertia::render('Dev/Plans', [
            'plans' => $plans,
        ]);
    }

    public function users()
    {
        $users = User::with('roles')
            ->orderByDesc('created_at')
            ->paginate(50)
            ->through(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'last_name'  => $u->last_name,
                'email'      => $u->email,
                'roles'      => $u->roles->pluck('name'),
                'created_at' => $u->created_at,
            ]);

        return Inertia::render('Dev/Users', [
            'users' => $users,
        ]);
    }
}
