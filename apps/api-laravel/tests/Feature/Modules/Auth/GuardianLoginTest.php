<?php

namespace Tests\Feature\Modules\Auth;

use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianLoginTest extends TestCase
{
    use RefreshDatabase;

    private Guardian $guardian;
    private Guardian $unverified;
    private Guardian $inactive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = Guardian::factory()->create([
            'email' => 'guardian@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => true,
        ]);

        $this->unverified = Guardian::factory()->create([
            'email' => 'unverified@test.com',
            'password' => 'password',
            'email_verified_at' => null,
            'active' => true,
        ]);

        $this->inactive = Guardian::factory()->create([
            'email' => 'inactive@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => false,
        ]);
    }

    public function test_successful_login_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/login', [
            'email' => 'guardian@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_invalid_password_returns_401_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/login', [
            'email' => 'guardian@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['code' => 'INVALID_CREDENTIALS']);
    }

    public function test_unverified_email_returns_403_email_not_verified(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/login', [
            'email' => 'unverified@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['code' => 'EMAIL_NOT_VERIFIED']);
    }

    public function test_inactive_account_returns_403_account_inactive(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['code' => 'ACCOUNT_INACTIVE']);
    }

    public function test_sixth_attempt_returns_429_too_many_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/guardian/auth/login', [
                'email' => 'guardian@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/guardian/auth/login', [
            'email' => 'guardian@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJsonFragment(['code' => 'TOO_MANY_ATTEMPTS']);
    }

    public function test_authenticated_logout_returns_200(): void
    {
        $token = $this->guardian->createToken('auth')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/guardian/auth/logout');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_logout_returns_401(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/logout');

        $response->assertStatus(401);
    }
}
