<?php

use App\Models\PasswordResetAuditEvent;
use App\Models\User;
use App\Services\Auth\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

function makeRequest(): Request
{
    return Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'test-agent']);
}

it('on PASSWORD_RESET: rehashes password, rotates remember_token, deletes sessions, writes audit row', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    DB::table('sessions')->insert([
        'id' => 'session-x',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => base64_encode('{}'),
        'last_activity' => now()->timestamp,
    ]);

    $service = app(PasswordResetService::class);
    $status = $service->reset($user->email, $token, 'NewSecure1234PW', makeRequest());

    expect($status)->toBe(Password::PASSWORD_RESET);
    expect(Hash::check('NewSecure1234PW', $user->fresh()->password))->toBeTrue();
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
    expect(PasswordResetAuditEvent::where('event_type', 'password_changed')
        ->where('outcome', 'accepted')->count())->toBe(1);
});

it('on INVALID_TOKEN: writes token_rejected audit row and does not modify user', function () {
    $user = User::factory()->create(['password' => Hash::make('original-password')]);
    $originalHash = $user->password;

    $service = app(PasswordResetService::class);
    $status = $service->reset($user->email, 'bad-token', 'NewSecure1234PW', makeRequest());

    expect($status)->toBe(Password::INVALID_TOKEN);
    expect($user->fresh()->password)->toBe($originalHash);
    expect(PasswordResetAuditEvent::where('event_type', 'token_rejected')
        ->where('outcome', 'rejected')
        ->where('reason', 'invalid')
        ->count())->toBe(1);
});

it('recordLinkOpened writes link_opened:accepted audit row with email_hash', function () {
    $user = User::factory()->create(['email' => 'audit@example.com']);

    $service = app(PasswordResetService::class);
    $service->recordLinkOpened('audit@example.com', makeRequest());

    $row = PasswordResetAuditEvent::where('event_type', 'link_opened')->first();
    expect($row)->not->toBeNull()
        ->and($row->outcome)->toBe('accepted')
        ->and($row->email_hash)->toBe(PasswordResetAuditEvent::emailHash('audit@example.com'))
        ->and($row->user_id)->toBe($user->id);
});
