<?php

namespace App\Policies;

use App\Models\CheckIn;
use App\Models\User;

class CheckInPolicy
{
    /**
     * Nutritionist can view/update check-ins for their own clients.
     * Client can view their own check-ins.
     */
    public function view(User $user, CheckIn $checkIn): bool
    {
        if ($user->isNutritionist()) {
            return $checkIn->client?->nutritionist_id === $user->id;
        }

        if ($user->isClient()) {
            return $checkIn->client_id === $user->clientProfile?->id;
        }

        return false;
    }

    public function update(User $user, CheckIn $checkIn): bool
    {
        if ($user->isNutritionist()) {
            return $checkIn->client?->nutritionist_id === $user->id;
        }

        if ($user->isClient()) {
            return $checkIn->client_id === $user->clientProfile?->id;
        }

        return false;
    }
}
