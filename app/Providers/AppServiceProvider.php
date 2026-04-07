<?php

namespace App\Providers;

use App\Models\AnamnesisTemplate;
use App\Models\Appointment;
use App\Models\CheckIn;
use App\Models\ClientProfile;
use App\Models\Food;
use App\Models\NutritionalPlan;
use App\Models\Recipe;
use App\Policies\AnamnesisTemplatePolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\CheckInPolicy;
use App\Policies\ClientProfilePolicy;
use App\Policies\FoodPolicy;
use App\Policies\NutritionalPlanPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(NutritionalPlan::class, NutritionalPlanPolicy::class);
        Gate::policy(ClientProfile::class, ClientProfilePolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(Food::class, FoodPolicy::class);
        Gate::policy(AnamnesisTemplate::class, AnamnesisTemplatePolicy::class);
        Gate::policy(CheckIn::class, CheckInPolicy::class);

        // Dev role bypasses all authorization gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('dev')) {
                return true;
            }
        });
    }
}
