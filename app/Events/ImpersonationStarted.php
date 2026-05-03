<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ImpersonationStarted
{
    use Dispatchable;

    public function __construct(public User $admin, public User $target) {}
}
