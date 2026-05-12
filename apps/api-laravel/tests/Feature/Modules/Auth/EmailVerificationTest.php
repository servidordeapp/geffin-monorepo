<?php

use App\Modules\Administration\Events\SchoolAdminCreated;
use App\Modules\Administration\Models\SchoolAdmin;
use App\Modules\Auth\Notifications\AdminEmailVerificationNotification;
use App\Modules\Auth\Notifications\GuardianEmailVerificationNotification;
use App\Modules\Students\Events\GuardianCreated;
use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('GuardianCreated event triggers verification email', function () {
    Notification::fake();

    $guardian = Guardian::factory()->create(['email_verified_at' => null]);

    event(new GuardianCreated($guardian));

    Notification::assertSentTo($guardian, GuardianEmailVerificationNotification::class);
});

test('valid signed URL verifies guardian email', function () {
    $guardian = Guardian::factory()->create(['email_verified_at' => null]);

    $url = URL::temporarySignedRoute(
        'guardian.verification.verify',
        now()->addHours(144),
        ['id' => $guardian->id, 'hash' => sha1($guardian->email)]
    );

    $path = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

    $response = $this->getJson($path);

    $response->assertStatus(200);
    expect($guardian->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('expired verification link returns 400 LINK_EXPIRED', function () {
    $guardian = Guardian::factory()->create(['email_verified_at' => null]);

    $url = URL::temporarySignedRoute(
        'guardian.verification.verify',
        now()->subHour(),
        ['id' => $guardian->id, 'hash' => sha1($guardian->email)]
    );

    $path = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

    $response = $this->getJson($path);

    $response->assertStatus(400)
        ->assertJsonFragment(['code' => 'LINK_EXPIRED']);
});

test('already verified guardian returns 400 EMAIL_ALREADY_VERIFIED', function () {
    $guardian = Guardian::factory()->create(['email_verified_at' => now()]);

    $url = URL::temporarySignedRoute(
        'guardian.verification.verify',
        now()->addHours(144),
        ['id' => $guardian->id, 'hash' => sha1($guardian->email)]
    );

    $path = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

    $response = $this->getJson($path);

    $response->assertStatus(400)
        ->assertJsonFragment(['code' => 'EMAIL_ALREADY_VERIFIED']);
});

test('resend verification sends email and returns 200', function () {
    Notification::fake();

    $guardian = Guardian::factory()->create(['email_verified_at' => null]);
    $token = $guardian->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/guardian/auth/resend-verification');

    $response->assertStatus(200);
    Notification::assertSentTo($guardian, GuardianEmailVerificationNotification::class);
});

test('resend when already verified returns 400', function () {
    $guardian = Guardian::factory()->create(['email_verified_at' => now()]);
    $token = $guardian->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/guardian/auth/resend-verification');

    $response->assertStatus(400)
        ->assertJsonFragment(['code' => 'EMAIL_ALREADY_VERIFIED']);
});

test('second resend within 1 minute returns 429', function () {
    $guardian = Guardian::factory()->create(['email_verified_at' => null]);
    $token = $guardian->createToken('auth')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/guardian/auth/resend-verification');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/guardian/auth/resend-verification');

    $response->assertStatus(429)
        ->assertJsonFragment(['code' => 'TOO_MANY_ATTEMPTS']);
});

test('SchoolAdminCreated event triggers admin verification email', function () {
    Notification::fake();

    $admin = SchoolAdmin::factory()->create(['email_verified_at' => null]);

    event(new SchoolAdminCreated($admin));

    Notification::assertSentTo($admin, AdminEmailVerificationNotification::class);
});
