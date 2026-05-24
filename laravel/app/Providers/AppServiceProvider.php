<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Auth\PasswordResetRateLimiter;
use App\Services\Auth\PasswordResetService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PasswordResetRateLimiter::class);
        $this->app->singleton(PasswordResetService::class);
    }

    public function boot(): void
    {
        //
    }
}
