<?php

namespace App\Policies;

use App\Models\Food;
use App\Models\User;

class FoodPolicy
{
    /**
     * Shared (global) foods are viewable by all nutritionists.
     * Custom foods are only accessible to the nutritionist who created them.
     */
    public function update(User $user, Food $food): bool
    {
        return $food->nutritionist_id === null || $food->nutritionist_id === $user->id;
    }

    public function delete(User $user, Food $food): bool
    {
        return $food->nutritionist_id === null || $food->nutritionist_id === $user->id;
    }
}
