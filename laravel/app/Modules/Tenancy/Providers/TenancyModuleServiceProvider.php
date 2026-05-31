<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Providers;

use App\Models\User;
use App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant;
use App\Modules\Tenancy\Http\Middleware\OnlyCentralDomains;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Policies\TenantPolicy;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class TenancyModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerGates();
        $this->loadRoutes();
        $this->registerLivewireComponents();

        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority(BlockDeletedTenant::class);
        $kernel->prependToMiddlewarePriority(OnlyCentralDomains::class);
    }

    protected function registerGates(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);

        Gate::define('manage-tenants', function (User $user) {
            return $user->is_central_admin;
        });
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../routes.php');
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::addLocation(classNamespace: 'App\Modules\Tenancy\Livewire');
    }
}
