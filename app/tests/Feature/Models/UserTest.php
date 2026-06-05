<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('factory creates a persistable user', function () {
    $user = User::factory()->create();

    expect($user->exists)->toBeTrue()
        ->and($user->name)->not->toBeEmpty()
        ->and($user->email)->toContain('@');
});

test('mass assignable attributes are fillable', function () {
    $user = new User();

    expect($user->getFillable())->toEqualCanonicalizing(['name', 'email', 'password']);
});

test('sensitive attributes are hidden from serialization', function () {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKeys(['password', 'remember_token']);
});

test('password is hashed when set', function () {
    $user = User::factory()->create([
        'password' => 'plain-text-password',
    ]);

    expect($user->password)->not->toBe('plain-text-password')
        ->and(Hash::check('plain-text-password', $user->password))->toBeTrue();
});

test('email_verified_at is cast to a datetime', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('unverified state leaves email_verified_at null', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});
