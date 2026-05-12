<?php

namespace Tests\Feature\Modules\Auth;

use App\Modules\Administration\Models\SchoolAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    private SchoolAdmin $admin;
    private SchoolAdmin $unverified;
    private SchoolAdmin $inactive;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login.admin:admin@test.com|127.0.0.1');
        RateLimiter::clear('login.admin:unverified@test.com|127.0.0.1');
        RateLimiter::clear('login.admin:inactive@test.com|127.0.0.1');

        $this->admin = SchoolAdmin::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => true,
        ]);

        $this->unverified = SchoolAdmin::factory()->create([
            'email' => 'unverified@test.com',
            'password' => 'password',
            'email_verified_at' => null,
            'active' => true,
        ]);

        $this->inactive = SchoolAdmin::factory()->create([
            'email' => 'inactive@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => false,
        ]);
    }

    public function test_successful_admin_login_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_invalid_credentials_returns_401_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['code' => 'INVALID_CREDENTIALS']);
    }

    public function test_unverified_admin_email_returns_403_email_not_verified(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'unverified@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['code' => 'EMAIL_NOT_VERIFIED']);
    }

    public function test_inactive_admin_account_returns_403_account_inactive(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['code' => 'ACCOUNT_INACTIVE']);
    }

    public function test_admin_rate_limit_lockout_after_5_failures_returns_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/admin/auth/login', [
                'email' => 'admin@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJsonFragment(['code' => 'TOO_MANY_ATTEMPTS']);
    }

    public function test_authenticated_admin_logout_returns_200(): void
    {
        $token = $this->admin->createToken('auth')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/auth/logout');

        $response->assertStatus(200);
    }
}
