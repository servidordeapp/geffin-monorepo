<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Auth\Providers;

use App\Models\User;
use App\Modules\Tenant\Auth\Models\TenantUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;

class TenantAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The web guard is shared between the central admin app and the tenant
        // apps. Central users carry `is_central_admin`; tenant users live in the
        // tenant database and must resolve to TenantUser instead — otherwise the
        // authenticated row is hydrated as App\Models\User and reading the
        // (absent) is_central_admin column throws under strict mode.
        Event::listen(TenancyInitialized::class, function (): void {
            $this->swapAuthModel(TenantUser::class);
        });

        Event::listen(TenancyEnded::class, function (): void {
            $this->swapAuthModel(User::class);
        });
    }

    /**
     * @param  class-string  $model
     */
    protected function swapAuthModel(string $model): void
    {
        config(['auth.providers.users.model' => $model]);

        // Drop any already-resolved guard so the next resolution rebuilds the
        // Eloquent user provider against the swapped model.
        Auth::forgetGuards();
    }
}
