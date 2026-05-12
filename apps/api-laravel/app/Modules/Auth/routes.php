<?php

use App\Modules\Auth\Controllers\AdminAuthController;
use App\Modules\Auth\Controllers\GuardianAuthController;
use Illuminate\Support\Facades\Route;

// Guardian Auth
Route::prefix('api/v1/guardian/auth')->group(function () {
    Route::post('login', [GuardianAuthController::class, 'login']);
    Route::post('forgot-password', [GuardianAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [GuardianAuthController::class, 'resetPassword']);

    Route::get('verify-email/{id}/{hash}', [GuardianAuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('guardian.verification.verify');

    Route::middleware('auth:guardian')->group(function () {
        Route::post('logout', [GuardianAuthController::class, 'logout']);
        Route::post('resend-verification', [GuardianAuthController::class, 'resendVerification']);
    });
});

// Admin Auth
Route::prefix('api/v1/admin/auth')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);

    Route::get('verify-email/{id}/{hash}', [AdminAuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('admin.verification.verify');

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('resend-verification', [AdminAuthController::class, 'resendVerification']);
    });
});
