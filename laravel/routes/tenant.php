<?php

declare(strict_types=1);

use App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::middleware([
    'web',
    BlockDeletedTenant::class,
    InitializeTenancyByDomain::class,
])->group(function () {
    Route::get('/', function () {
        if (! tenancy()->initialized) {
            return redirect()->route('login');
        }

        return 'This is your multi-tenant application. The id of the current tenant is '.tenant('id');
    });
});
