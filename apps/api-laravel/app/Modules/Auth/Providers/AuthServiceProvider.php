<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Administration\Events\SchoolAdminCreated;
use App\Modules\Auth\Listeners\SendAdminEmailVerification;
use App\Modules\Auth\Listeners\SendGuardianEmailVerification;
use App\Modules\Students\Events\GuardianCreated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $listen = [
        GuardianCreated::class => [
            SendGuardianEmailVerification::class,
        ],
        SchoolAdminCreated::class => [
            SendAdminEmailVerification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(database_path('migrations/Auth'));
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
    }
}
