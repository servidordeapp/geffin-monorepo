<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        if (($_SERVER['APP_ENV'] ?? null) === 'testing' || ($_ENV['APP_ENV'] ?? null) === 'testing') {
            $middleware->remove(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
