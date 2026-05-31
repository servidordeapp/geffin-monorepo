<?php

declare(strict_types=1);

use App\Modules\Tenancy\Http\Controllers\Tenants\DestroyTenantController;
use App\Modules\Tenancy\Http\Controllers\Tenants\RestoreTenantController;
use App\Modules\Tenancy\Http\Controllers\Tenants\ShowTenantController;
use App\Modules\Tenancy\Http\Controllers\Tenants\StoreTenantController;
use App\Modules\Tenancy\Http\Controllers\Tenants\UpdateTenantController;
use App\Modules\Tenancy\Http\Middleware\OnlyCentralDomains;
use App\Modules\Tenancy\Livewire\Tenants\Create;
use App\Modules\Tenancy\Livewire\Tenants\Edit;
use App\Modules\Tenancy\Livewire\Tenants\Index;
use Illuminate\Support\Facades\Route;

Route::middleware([OnlyCentralDomains::class, 'auth', 'can:manage-tenants'])->prefix('admin/inquilinos')->name('tenants.')->group(function () {
    Route::get('/', Index::class)->name('index');
    Route::get('/criar', Create::class)->name('create');
    Route::post('/', StoreTenantController::class)->name('store');
    Route::get('/{tenant}', ShowTenantController::class)->name('show')->withTrashed();
    Route::get('/{tenant}/editar', Edit::class)->name('edit');
    Route::patch('/{tenant}', UpdateTenantController::class)->name('update');
    Route::delete('/{tenant}', DestroyTenantController::class)->name('destroy')->withTrashed();
    Route::post('/{tenant}/restaurar', RestoreTenantController::class)->name('restore')->withTrashed();
});
