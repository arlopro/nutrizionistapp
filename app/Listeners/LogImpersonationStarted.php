<?php

namespace App\Listeners;

use App\Events\ImpersonationStarted;

class LogImpersonationStarted
{
    public function handle(ImpersonationStarted $event): void
    {
        activity()
            ->causedBy($event->admin)
            ->performedOn($event->target)
            ->withProperties(['admin_id' => $event->admin->id, 'target_id' => $event->target->id])
            ->log('impersonate_start');
    }
}
