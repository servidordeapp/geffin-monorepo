<?php

use App\Modules\Students\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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
});

test('successful login returns token and user', function () {
    $response = $this->postJson('/api/v1/guardian/auth/login', [
        'email' => 'guardian@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('invalid password returns 401 INVALID_CREDENTIALS', function () {
    $response = $this->postJson('/api/v1/guardian/auth/login', [
        'email' => 'guardian@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonFragment(['code' => 'INVALID_CREDENTIALS']);
});

test('unverified email returns 403 EMAIL_NOT_VERIFIED', function () {
    $response = $this->postJson('/api/v1/guardian/auth/login', [
        'email' => 'unverified@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment(['code' => 'EMAIL_NOT_VERIFIED']);
});

test('inactive account returns 403 ACCOUNT_INACTIVE', function () {
    $response = $this->postJson('/api/v1/guardian/auth/login', [
        'email' => 'inactive@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment(['code' => 'ACCOUNT_INACTIVE']);
});

test('6th attempt returns 429 TOO_MANY_ATTEMPTS', function () {
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
});

test('authenticated logout returns 200', function () {
    $token = $this->guardian->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/guardian/auth/logout');

    $response->assertStatus(200);
});

test('unauthenticated logout returns 401', function () {
    $response = $this->postJson('/api/v1/guardian/auth/logout');

    $response->assertStatus(401);
});
