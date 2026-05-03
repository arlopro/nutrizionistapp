<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ImpersonationStopped
{
    use Dispatchable;

    public function __construct(public User $admin, public User $impersonated) {}
}
