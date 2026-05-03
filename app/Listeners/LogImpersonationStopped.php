<?php

namespace App\Listeners;

use App\Events\ImpersonationStopped;

class LogImpersonationStopped
{
    public function handle(ImpersonationStopped $event): void
    {
        activity()
            ->causedBy($event->admin)
            ->performedOn($event->impersonated)
            ->withProperties(['admin_id' => $event->admin->id, 'impersonated_id' => $event->impersonated->id])
            ->log('impersonate_stop');
    }
}
