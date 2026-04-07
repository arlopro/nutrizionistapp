<?php

namespace App\Policies;

use App\Models\AnamnesisTemplate;
use App\Models\User;

class AnamnesisTemplatePolicy
{
    public function view(User $user, AnamnesisTemplate $anamnesi): bool
    {
        return $user->id === $anamnesi->nutritionist_id;
    }

    public function update(User $user, AnamnesisTemplate $anamnesi): bool
    {
        return $user->id === $anamnesi->nutritionist_id;
    }

    public function delete(User $user, AnamnesisTemplate $anamnesi): bool
    {
        return $user->id === $anamnesi->nutritionist_id;
    }
}
