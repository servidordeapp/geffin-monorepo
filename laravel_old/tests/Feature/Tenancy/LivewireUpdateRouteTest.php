<?php

declare(strict_types=1);

use App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

it('wraps the livewire update route in tenancy middleware', function () {
    $route = Route::getRoutes()->getByName('livewire.update');

    expect($route)->not->toBeNull();

    $middleware = $route->gatherMiddleware();

    // Without these, the Livewire AJAX endpoint runs in the central context:
    // the tenant session (and its CSRF token) is read from the wrong database,
    // producing a 419 and making tenant login impossible.
    expect($middleware)
        ->toContain(InitializeTenancyByDomain::class)
        ->toContain(BlockDeletedTenant::class);
});
