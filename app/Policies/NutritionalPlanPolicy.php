<?php

namespace App\Policies;

use App\Models\NutritionalPlan;
use App\Models\User;

class NutritionalPlanPolicy
{
    public function view(User $user, NutritionalPlan $plan): bool
    {
        return $user->id === $plan->nutritionist_id;
    }

    public function update(User $user, NutritionalPlan $plan): bool
    {
        return $user->id === $plan->nutritionist_id;
    }

    public function delete(User $user, NutritionalPlan $plan): bool
    {
        return $user->id === $plan->nutritionist_id;
    }
}
