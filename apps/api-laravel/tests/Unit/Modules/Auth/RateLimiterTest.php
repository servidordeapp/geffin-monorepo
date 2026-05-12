<?php

namespace Tests\Unit\Modules\Auth;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimiterTest extends TestCase
{
    public function test_login_guardian_limiter_uses_email_ip_key(): void
    {
        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $limit = RateLimiter::limiter('login.guardian')($request);

        $this->assertInstanceOf(Limit::class, $limit);

        $key = (fn () => $this->key)->bindTo($limit, $limit)();
        $this->assertSame('test@test.com|127.0.0.1', $key);
    }

    public function test_login_guardian_limiter_allows_5_attempts_per_15_minutes(): void
    {
        $request = Request::create('/login', 'POST', ['email' => 'test@test.com']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $limit = RateLimiter::limiter('login.guardian')($request);

        $maxAttempts = (fn () => $this->maxAttempts)->bindTo($limit, $limit)();
        $decayMinutes = (fn () => $this->decayMinutes)->bindTo($limit, $limit)();

        $this->assertSame(5, $maxAttempts);
        $this->assertSame(15, $decayMinutes);
    }

    public function test_resend_guardian_limiter_uses_email_key(): void
    {
        $request = Request::create('/resend', 'POST', ['email' => 'test@test.com']);

        $limit = RateLimiter::limiter('resend.guardian')($request);

        $this->assertInstanceOf(Limit::class, $limit);

        $key = (fn () => $this->key)->bindTo($limit, $limit)();
        $this->assertSame('email:test@test.com', $key);
    }

    public function test_resend_guardian_limiter_allows_1_attempt_per_minute(): void
    {
        $request = Request::create('/resend', 'POST', ['email' => 'test@test.com']);

        $limit = RateLimiter::limiter('resend.guardian')($request);

        $maxAttempts = (fn () => $this->maxAttempts)->bindTo($limit, $limit)();
        $decayMinutes = (fn () => $this->decayMinutes)->bindTo($limit, $limit)();

        $this->assertSame(1, $maxAttempts);
        $this->assertSame(1, $decayMinutes);
    }
}
