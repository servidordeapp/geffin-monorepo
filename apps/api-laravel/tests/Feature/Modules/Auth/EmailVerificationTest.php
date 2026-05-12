<?php

namespace Tests\Feature\Modules\Auth;

use App\Modules\Administration\Events\SchoolAdminCreated;
use App\Modules\Administration\Models\SchoolAdmin;
use App\Modules\Auth\Notifications\AdminEmailVerificationNotification;
use App\Modules\Auth\Notifications\GuardianEmailVerificationNotification;
use App\Modules\Students\Events\GuardianCreated;
use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_guardian_created_event_triggers_verification_email(): void
    {
        Notification::fake();

        $guardian = Guardian::factory()->create(['email_verified_at' => null]);

        event(new GuardianCreated($guardian));

        Notification::assertSentTo($guardian, GuardianEmailVerificationNotification::class);
    }

    public function test_valid_signed_url_verifies_guardian_email(): void
    {
        $guardian = Guardian::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'guardian.verification.verify',
            now()->addHours(144),
            ['id' => $guardian->id, 'hash' => sha1($guardian->email)]
        );

        $path = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

        $response = $this->getJson($path);

        $response->assertStatus(200);
        $this->assertTrue($guardian->fresh()->hasVerifiedEmail());
    }

    public function test_expired_verification_link_returns_400_link_expired(): void
    {
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
    }

    public function test_already_verified_guardian_returns_400_email_already_verified(): void
    {
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
    }

    public function test_resend_verification_sends_email_and_returns_200(): void
    {
        Notification::fake();

        $guardian = Guardian::factory()->create(['email_verified_at' => null]);

        $response = $this->postJson('/api/v1/guardian/auth/resend-verification', [
            'email' => $guardian->email,
        ]);

        $response->assertStatus(200);
        Notification::assertSentTo($guardian, GuardianEmailVerificationNotification::class);
    }

    public function test_resend_for_already_verified_email_returns_generic_200(): void
    {
        Notification::fake();

        $guardian = Guardian::factory()->create(['email_verified_at' => now()]);

        $response = $this->postJson('/api/v1/guardian/auth/resend-verification', [
            'email' => $guardian->email,
        ]);

        $response->assertStatus(200);
        Notification::assertNothingSent();
    }

    public function test_resend_for_unknown_email_returns_generic_200(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/guardian/auth/resend-verification', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200);
        Notification::assertNothingSent();
    }

    public function test_second_resend_within_1_minute_returns_429(): void
    {
        $guardian = Guardian::factory()->create(['email_verified_at' => null]);

        $this->postJson('/api/v1/guardian/auth/resend-verification', [
            'email' => $guardian->email,
        ]);

        $response = $this->postJson('/api/v1/guardian/auth/resend-verification', [
            'email' => $guardian->email,
        ]);

        $response->assertStatus(429)
            ->assertJsonFragment(['code' => 'TOO_MANY_ATTEMPTS']);
    }

    public function test_school_admin_created_event_triggers_admin_verification_email(): void
    {
        Notification::fake();

        $admin = SchoolAdmin::factory()->create(['email_verified_at' => null]);

        event(new SchoolAdminCreated($admin));

        Notification::assertSentTo($admin, AdminEmailVerificationNotification::class);
    }
}
