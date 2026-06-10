<?php

declare(strict_types=1);

use App\Models\PasswordResetAuditEvent;
use App\Models\User;
use App\Notifications\PasswordResetRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the forgot password link on the login page pointing to password.request', function () {
    $this->get(route('login'))
        ->assertSee(route('password.request'));
});

it('shows inline error for invalid email format and writes no audit row', function () {
    Notification::fake();

    Livewire::test('auth.forgot-password')
        ->set('email', 'not-an-email')
        ->call('requestReset')
        ->assertHasErrors(['email']);

    expect(PasswordResetAuditEvent::count())->toBe(0);
    Notification::assertNothingSent();
});

it('returns identical status string for known and unknown email', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'known@example.com']);

    $knownComponent = Livewire::test('auth.forgot-password')
        ->set('email', 'known@example.com')
        ->call('requestReset');

    $unknownComponent = Livewire::test('auth.forgot-password')
        ->set('email', 'unknown@example.com')
        ->call('requestReset');

    expect($knownComponent->get('status'))->toBe($unknownComponent->get('status'));
});

it('queues a reset notification for a known email and creates a token row', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'user@example.com']);

    Livewire::test('auth.forgot-password')
        ->set('email', 'user@example.com')
        ->call('requestReset')
        ->assertHasNoErrors();

    Notification::assertSentTo($user, PasswordResetRequested::class);

    expect(DB::table('password_reset_tokens')
        ->where('email', 'user@example.com')
        ->count())->toBe(1);
});

it('replaces previous token on re-request for same email', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'user@example.com']);

    Livewire::test('auth.forgot-password')
        ->set('email', 'user@example.com')
        ->call('requestReset');

    Livewire::test('auth.forgot-password')
        ->set('email', 'user@example.com')
        ->call('requestReset');

    expect(DB::table('password_reset_tokens')
        ->where('email', 'user@example.com')
        ->count())->toBe(1);
});

it('writes one requested:accepted audit row per submission', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'user@example.com']);

    Livewire::test('auth.forgot-password')
        ->set('email', 'user@example.com')
        ->call('requestReset');

    $auditRow = PasswordResetAuditEvent::where('event_type', 'requested')->first();
    expect($auditRow)->not->toBeNull()
        ->and($auditRow->outcome)->toBe('accepted')
        ->and($auditRow->user_id)->toBe($user->id);

    Livewire::test('auth.forgot-password')
        ->set('email', 'unknown@example.com')
        ->call('requestReset');

    $unknownRow = PasswordResetAuditEvent::where('event_type', 'requested')
        ->whereNull('user_id')
        ->first();
    expect($unknownRow)->not->toBeNull()
        ->and($unknownRow->outcome)->toBe('accepted');
});
