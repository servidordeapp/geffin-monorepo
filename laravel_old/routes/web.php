<?php

declare(strict_types=1);

use App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

// Universal routes: on a tenant hostname these initialize tenancy (so auth
// resolves against the tenant database); on a central domain the tenancy
// middleware short-circuits to the central context (see TenancyServiceProvider
// configureTenancyOnFail + BlockDeletedTenant central-domain passthrough).
Route::middleware([
    BlockDeletedTenant::class,
    InitializeTenancyByDomain::class,
])->group(function () {
    Route::middleware('guest')->group(function () {
        Route::livewire('/login', 'auth.login')->name('login');
        Route::livewire('/senha/esqueci', 'auth.forgot-password')->name('password.request');
        Route::livewire('/senha/redefinir/{token}', 'auth.reset-password')
            ->middleware('signed')
            ->name('password.reset');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

        Route::post('/logout', function () {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login');
        })->name('logout');
    });

    Route::get('/', fn () => redirect()->route('login'));
});
