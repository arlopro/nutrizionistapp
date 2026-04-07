<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\NutritionalPlan;
use App\Models\User;
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
                'id'                  => $u->id,
                'name'                => $u->name,
                'last_name'           => $u->last_name,
                'email'               => $u->email,
                'created_at'          => $u->created_at,
                'clients_count'       => $u->clients_count,
                'active_clients_count'=> $u->active_clients_count,
                'specialization'      => $u->nutritionistProfile?->specialization,
                'city'                => $u->nutritionistProfile?->city,
                'plans_count'         => NutritionalPlan::where('nutritionist_id', $u->id)->where('is_template', false)->count(),
                'appointments_count'  => Appointment::where('nutritionist_id', $u->id)->count(),
            ]);

        return Inertia::render('Dev/Nutritionists', [
            'nutritionists' => $nutritionists,
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
