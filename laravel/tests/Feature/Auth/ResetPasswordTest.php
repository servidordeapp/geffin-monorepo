<?php

declare(strict_types=1);

use App\Models\PasswordResetAuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeResetToken(User $user): string
{
    return Password::broker()->createToken($user);
}

it('shows validation error for a password not meeting policy', function () {
    $user = User::factory()->create();
    $token = makeResetToken($user);

    $signedUrl = URL::temporarySignedRoute(
        'password.reset',
        now()->addMinutes(60),
        ['token' => $token, 'email' => $user->email],
    );

    $query = parse_url($signedUrl, PHP_URL_QUERY);
    parse_str($query, $params);

    Livewire::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'abc')
        ->set('password_confirmation', 'abc')
        ->call('updatePassword')
        ->assertHasErrors(['password']);

    expect(PasswordResetAuditEvent::where('event_type', 'password_changed')->count())->toBe(0);
});

it('hashes the new password, rotates remember_token, purges sessions and writes audit row on success', function () {
    $user = User::factory()->create();
    $token = makeResetToken($user);

    DB::table('sessions')->insert([
        ['id' => 'session-a', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => base64_encode('{}'), 'last_activity' => now()->timestamp],
        ['id' => 'session-b', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => base64_encode('{}'), 'last_activity' => now()->timestamp],
    ]);

    $oldToken = $user->remember_token;

    Livewire::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'NewPass1234ABC')
        ->set('password_confirmation', 'NewPass1234ABC')
        ->call('updatePassword');

    $fresh = $user->fresh();
    expect(Hash::check('NewPass1234ABC', $fresh->password))->toBeTrue()
        ->and($fresh->remember_token)->not->toBe($oldToken)
        ->and(strlen($fresh->remember_token))->toBe(60);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);

    expect(PasswordResetAuditEvent::where('event_type', 'password_changed')
        ->where('outcome', 'accepted')
        ->count())->toBe(1);
});

it('redirects to login with success flash on successful reset', function () {
    $user = User::factory()->create();
    $token = makeResetToken($user);

    Livewire::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'NewPass1234ABC')
        ->set('password_confirmation', 'NewPass1234ABC')
        ->call('updatePassword')
        ->assertRedirect(route('login'));
});

it('shows token error and writes token_rejected audit row for a reused token', function () {
    $user = User::factory()->create();
    $token = makeResetToken($user);

    Livewire::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'NewPass1234ABC')
        ->set('password_confirmation', 'NewPass1234ABC')
        ->call('updatePassword');

    $result = Livewire::test('auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'AnotherPass456DEF')
        ->set('password_confirmation', 'AnotherPass456DEF')
        ->call('updatePassword');

    expect($result->get('tokenError'))->not->toBeNull();

    expect(PasswordResetAuditEvent::where('event_type', 'token_rejected')
        ->where('outcome', 'rejected')
        ->count())->toBeGreaterThanOrEqual(1);
});

it('returns 403 for a tampered signed URL', function () {
    $user = User::factory()->create();
    $token = makeResetToken($user);

    $url = URL::temporarySignedRoute(
        'password.reset',
        now()->addMinutes(60),
        ['token' => $token, 'email' => $user->email],
    );

    $tamperedUrl = $url.'tampered';

    $this->get($tamperedUrl)->assertForbidden();
});
