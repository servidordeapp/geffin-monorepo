<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

test('login guardian limiter uses email|ip key', function () {
    $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);
    $request->server->set('REMOTE_ADDR', '127.0.0.1');

    $limit = RateLimiter::limiter('login.guardian')($request);

    expect($limit)->toBeInstanceOf(Limit::class);

    $key = (fn () => $this->key)->bindTo($limit, $limit)();
    expect($key)->toBe('test@test.com|127.0.0.1');
});

test('login guardian limiter allows 5 attempts per 15 minutes', function () {
    $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);
    $request->server->set('REMOTE_ADDR', '127.0.0.1');

    $limit = RateLimiter::limiter('login.guardian')($request);

    $maxAttempts = (fn () => $this->maxAttempts)->bindTo($limit, $limit)();
    $decayMinutes = (fn () => $this->decayMinutes)->bindTo($limit, $limit)();

    expect($maxAttempts)->toBe(5);
    expect($decayMinutes)->toBe(15);
});

test('resend guardian limiter uses user id key', function () {
    $guardian = new \App\Modules\Students\Models\Guardian();
    $guardian->id = 'test-uuid-123';

    $request = Request::create('/resend', 'POST');
    $request->setUserResolver(fn ($guard = null) => $guard === 'guardian' ? $guardian : null);

    $limit = RateLimiter::limiter('resend.guardian')($request);

    expect($limit)->toBeInstanceOf(Limit::class);
});

test('resend guardian limiter allows 1 attempt per minute', function () {
    $guardian = new \App\Modules\Students\Models\Guardian();
    $guardian->id = 'test-uuid-123';

    $request = Request::create('/resend', 'POST');
    $request->setUserResolver(fn ($guard = null) => $guard === 'guardian' ? $guardian : null);

    $limit = RateLimiter::limiter('resend.guardian')($request);

    $maxAttempts = (fn () => $this->maxAttempts)->bindTo($limit, $limit)();
    $decayMinutes = (fn () => $this->decayMinutes)->bindTo($limit, $limit)();

    expect($maxAttempts)->toBe(1);
    expect($decayMinutes)->toBe(1);
});
