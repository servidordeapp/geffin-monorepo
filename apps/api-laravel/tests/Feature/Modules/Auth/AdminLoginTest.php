<?php

use App\Modules\Administration\Models\SchoolAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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
});

test('successful admin login returns token and user', function () {
    $response = $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('invalid credentials returns 401 INVALID_CREDENTIALS', function () {
    $response = $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'admin@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonFragment(['code' => 'INVALID_CREDENTIALS']);
});

test('unverified admin email returns 403 EMAIL_NOT_VERIFIED', function () {
    $response = $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'unverified@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment(['code' => 'EMAIL_NOT_VERIFIED']);
});

test('inactive admin account returns 403 ACCOUNT_INACTIVE', function () {
    $response = $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'inactive@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment(['code' => 'ACCOUNT_INACTIVE']);
});

test('admin rate limit lockout after 5 failures returns 429', function () {
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
});

test('authenticated admin logout returns 200', function () {
    $token = $this->admin->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/admin/auth/logout');

    $response->assertStatus(200);
});
