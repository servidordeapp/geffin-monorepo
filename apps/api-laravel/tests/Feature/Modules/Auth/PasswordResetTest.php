<?php

use App\Modules\Administration\Models\SchoolAdmin;
use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
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
});

test('forgot password returns generic 200 for valid email', function () {
    $response = $this->postJson('/api/v1/guardian/auth/forgot-password', [
        'email' => 'guardian@test.com',
    ]);

    $response->assertStatus(200);
});

test('forgot password returns generic 200 for unknown email (no info leak)', function () {
    $response = $this->postJson('/api/v1/guardian/auth/forgot-password', [
        'email' => 'nonexistent@test.com',
    ]);

    $response->assertStatus(200);
});

test('valid reset token updates password', function () {
    $token = Password::broker('guardians')->createToken($this->guardian);

    $response = $this->postJson('/api/v1/guardian/auth/reset-password', [
        'token' => $token,
        'email' => 'guardian@test.com',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ]);

    $response->assertStatus(200);
});

test('reset token is invalidated after use', function () {
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
});

test('invalid reset token returns 422 INVALID_RESET_TOKEN', function () {
    $response = $this->postJson('/api/v1/guardian/auth/reset-password', [
        'token' => 'invalid-token',
        'email' => 'guardian@test.com',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['code' => 'INVALID_RESET_TOKEN']);
});

test('all sanctum tokens revoked after password reset', function () {
    $this->guardian->createToken('session1');
    $this->guardian->createToken('session2');
    expect($this->guardian->tokens()->count())->toBe(2);

    $token = Password::broker('guardians')->createToken($this->guardian);

    $this->postJson('/api/v1/guardian/auth/reset-password', [
        'token' => $token,
        'email' => 'guardian@test.com',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ]);

    expect($this->guardian->fresh()->tokens()->count())->toBe(0);
});

test('admin forgot password returns generic 200', function () {
    $response = $this->postJson('/api/v1/admin/auth/forgot-password', [
        'email' => 'admin@test.com',
    ]);

    $response->assertStatus(200);
});

test('admin valid reset token updates password', function () {
    $token = Password::broker('admins')->createToken($this->admin);

    $response = $this->postJson('/api/v1/admin/auth/reset-password', [
        'token' => $token,
        'email' => 'admin@test.com',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ]);

    $response->assertStatus(200);
});
