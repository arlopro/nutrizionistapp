<?php

namespace App\Policies;

use App\Models\ClientProfile;
use App\Models\User;

class ClientProfilePolicy
{
    public function view(User $user, ClientProfile $client): bool
    {
        return $user->id === $client->nutritionist_id;
    }

    public function update(User $user, ClientProfile $client): bool
    {
        return $user->id === $client->nutritionist_id;
    }

    public function delete(User $user, ClientProfile $client): bool
    {
        return $user->id === $client->nutritionist_id;
    }
}
