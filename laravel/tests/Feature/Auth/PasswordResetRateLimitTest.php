<?php

declare(strict_types=1);

use App\Models\PasswordResetAuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    RateLimiter::clear('password-reset:email:'.sha1('throttled@example.com'));
});

it('throttles after 5 requests for the same email in one hour', function () {
    User::factory()->create(['email' => 'throttled@example.com']);

    for ($i = 0; $i < 5; $i++) {
        Livewire::test('auth.forgot-password')
            ->set('email', 'throttled@example.com')
            ->call('requestReset');
    }

    $result = Livewire::test('auth.forgot-password')
        ->set('email', 'throttled@example.com')
        ->call('requestReset');

    expect($result->get('status'))->toBe(__('passwords.throttled'));

    expect(PasswordResetAuditEvent::where('event_type', 'request_throttled')
        ->where('outcome', 'throttled')
        ->count())->toBeGreaterThanOrEqual(1);
});

it('throttles after 20 requests from the same IP across different emails', function () {
    for ($i = 0; $i < 20; $i++) {
        User::factory()->create(['email' => "user{$i}@example.com"]);
        Livewire::test('auth.forgot-password')
            ->set('email', "user{$i}@example.com")
            ->call('requestReset');
    }

    User::factory()->create(['email' => 'overflow@example.com']);
    $result = Livewire::test('auth.forgot-password')
        ->set('email', 'overflow@example.com')
        ->call('requestReset');

    expect($result->get('status'))->toBe(__('passwords.throttled'));
});

it('resets the limiter after one hour passes', function () {
    User::factory()->create(['email' => 'throttled@example.com']);

    for ($i = 0; $i < 6; $i++) {
        Livewire::test('auth.forgot-password')
            ->set('email', 'throttled@example.com')
            ->call('requestReset');
    }

    $this->travel(61)->minutes();
    RateLimiter::clear('password-reset:email:'.sha1('throttled@example.com'));

    $result = Livewire::test('auth.forgot-password')
        ->set('email', 'throttled@example.com')
        ->call('requestReset');

    expect($result->get('status'))->toBe(__('passwords.sent'));
});
