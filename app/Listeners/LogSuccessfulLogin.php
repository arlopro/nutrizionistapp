<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $user->last_login_at = now();
        $user->saveQuietly();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['ip' => request()->ip()])
            ->log('login');
    }
}
