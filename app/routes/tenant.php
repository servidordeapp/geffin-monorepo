<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->name('tenant.')->group(function () {
    Route::get('/', fn () => redirect()->route('tenant.dashboard'));

    Route::livewire('/logar', 'pages::tenant.auth.login')->name('login');

    Route::middleware('auth:tenant')->group(function () {
        Route::get('/painel', fn () => view('pages.tenant.dashboard'))->name('dashboard');

        Route::post('/deslogar', function (Request $request) {
            Auth::guard('tenant')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tenant.login');
        })->name('logout');
    });
});
