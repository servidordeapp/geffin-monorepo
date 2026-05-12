<?php

namespace Tests\Feature\Modules\Auth;

use App\Modules\Administration\Models\SchoolAdmin;
use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private Guardian $guardian;
    private SchoolAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = Guardian::factory()->create([
            'email' => 'guardian@test.com',
            'password' => 'oldpassword',
            'email_verified_at' => now(),
        ]);

        $this->admin = SchoolAdmin::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'oldpassword',
            'email_verified_at' => now(),
        ]);
    }

    public function test_forgot_password_returns_generic_200_for_valid_email(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/forgot-password', [
            'email' => 'guardian@test.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_forgot_password_returns_generic_200_for_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/forgot-password', [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_valid_reset_token_updates_password(): void
    {
        $token = Password::broker('guardians')->createToken($this->guardian);

        $response = $this->postJson('/api/v1/guardian/auth/reset-password', [
            'token' => $token,
            'email' => 'guardian@test.com',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertStatus(200);
    }

    public function test_reset_token_is_invalidated_after_use(): void
    {
        $token = Password::broker('guardians')->createToken($this->guardian);

        $this->postJson('/api/v1/guardian/auth/reset-password', [
            'token' => $token,
            'email' => 'guardian@test.com',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response = $this->postJson('/api/v1/guardian/auth/reset-password', [
            'token' => $token,
            'email' => 'guardian@test.com',
            'password' => 'anotherpassword1',
            'password_confirmation' => 'anotherpassword1',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['code' => 'INVALID_RESET_TOKEN']);
    }

    public function test_invalid_reset_token_returns_422_invalid_reset_token(): void
    {
        $response = $this->postJson('/api/v1/guardian/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'guardian@test.com',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['code' => 'INVALID_RESET_TOKEN']);
    }

    public function test_all_sanctum_tokens_revoked_after_password_reset(): void
    {
        $this->guardian->createToken('session1');
        $this->guardian->createToken('session2');
        $this->assertSame(2, $this->guardian->tokens()->count());

        $token = Password::broker('guardians')->createToken($this->guardian);

        $this->postJson('/api/v1/guardian/auth/reset-password', [
            'token' => $token,
            'email' => 'guardian@test.com',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $this->assertSame(0, $this->guardian->fresh()->tokens()->count());
    }

    public function test_admin_forgot_password_returns_generic_200(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/forgot-password', [
            'email' => 'admin@test.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_valid_reset_token_updates_password(): void
    {
        $token = Password::broker('admins')->createToken($this->admin);

        $response = $this->postJson('/api/v1/admin/auth/reset-password', [
            'token' => $token,
            'email' => 'admin@test.com',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response->assertStatus(200);
    }
}
