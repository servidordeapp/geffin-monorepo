<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('has less than 50ms response time difference between known and unknown email at p95', function () {
    Notification::fake();
    $n = 100;
    $knownTimes = [];
    $unknownTimes = [];

    // Pre-create all users BEFORE timing starts to avoid bcrypt in the loop
    $knownEmails = array_map(fn ($i) => "known{$i}@enum.test", range(0, $n - 1));
    foreach ($knownEmails as $email) {
        User::factory()->create(['email' => $email]);
    }

    // Warmup: heat up framework and view compilation equally for both branches
    RateLimiter::clear('password-reset:email:'.sha1($knownEmails[0]));
    RateLimiter::clear('password-reset:ip:127.0.0.1');
    Livewire::test('auth.forgot-password')->set('email', $knownEmails[0])->call('requestReset');

    RateLimiter::clear('password-reset:email:'.sha1('warmup-unknown@enum.test'));
    RateLimiter::clear('password-reset:ip:127.0.0.1');
    Livewire::test('auth.forgot-password')->set('email', 'warmup-unknown@enum.test')->call('requestReset');

    // Interleave known/unknown to avoid ordering bias
    for ($i = 0; $i < $n; $i++) {
        $knownEmail = "known{$i}@enum.test";
        $unknownEmail = "unknown{$i}@enum.test";

        RateLimiter::clear('password-reset:email:'.sha1($knownEmail));
        RateLimiter::clear('password-reset:ip:127.0.0.1');

        $start = microtime(true);
        Livewire::test('auth.forgot-password')->set('email', $knownEmail)->call('requestReset');
        $knownTimes[] = (microtime(true) - $start) * 1000;

        RateLimiter::clear('password-reset:email:'.sha1($unknownEmail));
        RateLimiter::clear('password-reset:ip:127.0.0.1');

        $start = microtime(true);
        Livewire::test('auth.forgot-password')->set('email', $unknownEmail)->call('requestReset');
        $unknownTimes[] = (microtime(true) - $start) * 1000;
    }

    sort($knownTimes);
    sort($unknownTimes);
    $p95Index = (int) ceil(0.95 * $n) - 1;
    $knownP95 = $knownTimes[$p95Index];
    $unknownP95 = $unknownTimes[$p95Index];

    // Only known-email paths dispatch notifications (broker skips INVALID_USER paths).
    Notification::assertCount($n + 1); // n+1: 1 warmup + n known iterations

    // SC-005: p95 delta < 50ms. Both paths go through PasswordBroker::sendResetLink(),
    // which wraps a Timebox(200ms) — unknown emails return INVALID_USER early but still
    // block for the full 200ms, equalising timing without a manual usleep.
    expect(abs($knownP95 - $unknownP95))->toBeLessThan(50);
});
