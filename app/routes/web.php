<?php

declare(strict_types=1);

use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantDomainController;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/logar', 'pages::auth.login')->name('login');
Route::livewire('/esqueci-senha', 'pages::auth.forgot-password')->name('password.request');
Route::livewire('/redefinir-senha/{token}', 'pages::auth.reset-password')->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/painel', fn () => view('pages.dashboard', [
        'tenantCount' => Tenant::count(),
    ]))->name('dashboard');

    Route::resource('tenants', TenantController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::resource('tenants.domains', TenantDomainController::class)
        ->only(['store', 'destroy'])
        ->scoped();

    Route::post('/deslogar', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
