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
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class TenancyModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerGates();
        $this->loadRoutes();
        $this->registerLivewireComponents();
        $this->makeLivewireTenantAware();

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

    /**
     * Make Livewire's update endpoint tenant-aware.
     *
     * By default Livewire registers its update route with only the `web`
     * middleware, so the AJAX call that drives every tenant Livewire component
     * (e.g. the login form) runs in the central context: the session — and with
     * it the CSRF token saved during the tenant GET — is read from the central
     * database, producing a 419 "Página expirada" and making tenant login
     * impossible. Wrapping the route in the same tenancy middleware as the
     * universal routes in routes/web.php keeps it on the central domain (via the
     * BlockDeletedTenant passthrough + InitializeTenancyByDomain onFail handler)
     * while initializing tenancy on tenant domains.
     */
    protected function makeLivewireTenantAware(): void
    {
        Livewire::setUpdateRoute(function ($handle, string $updatePath) {
            return Route::post($updatePath, $handle)->middleware([
                'web',
                BlockDeletedTenant::class,
                InitializeTenancyByDomain::class,
            ]);
        });
    }
}
