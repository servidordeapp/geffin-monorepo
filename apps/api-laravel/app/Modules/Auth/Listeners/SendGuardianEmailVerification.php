<?php

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Notifications\GuardianEmailVerificationNotification;
use App\Modules\Students\Events\GuardianCreated;

class SendGuardianEmailVerification
{
    public function handle(GuardianCreated $event): void
    {
        $event->guardian->notify(new GuardianEmailVerificationNotification());
    }
}
