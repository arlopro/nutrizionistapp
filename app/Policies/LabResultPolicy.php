<?php

namespace App\Policies;

use App\Models\LabResult;
use App\Models\User;

class LabResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isNutritionist();
    }

    public function view(User $user, LabResult $labResult): bool
    {
        if ($user->isNutritionist()) {
            return $labResult->client?->nutritionist_id === $user->id;
        }

        if ($user->isClient()) {
            return $labResult->client_id === $user->clientProfile?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isNutritionist();
    }

    public function update(User $user, LabResult $labResult): bool
    {
        return $user->isNutritionist() && $labResult->client?->nutritionist_id === $user->id;
    }

    public function delete(User $user, LabResult $labResult): bool
    {
        return $user->isNutritionist() && $labResult->client?->nutritionist_id === $user->id;
    }
}
