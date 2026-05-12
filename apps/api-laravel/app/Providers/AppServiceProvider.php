<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('login.guardian', function (Request $request) {
            return Limit::perMinutes(15, 5)
                ->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('login.admin', function (Request $request) {
            return Limit::perMinutes(15, 5)
                ->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('resend.guardian', function (Request $request) {
            return Limit::perMinute(1)
                ->by('user:'.$request->user('guardian')?->id);
        });

        RateLimiter::for('resend.admin', function (Request $request) {
            return Limit::perMinute(1)
                ->by('user:'.$request->user('admin')?->id);
        });
    }
}
