<?php

namespace App\Modules\Auth\Listeners;

use App\Modules\Administration\Events\SchoolAdminCreated;
use App\Modules\Auth\Notifications\AdminEmailVerificationNotification;

class SendAdminEmailVerification
{
    public function handle(SchoolAdminCreated $event): void
    {
        $event->schoolAdmin->notify(new AdminEmailVerificationNotification());
    }
}
