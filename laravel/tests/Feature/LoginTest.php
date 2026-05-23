<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the login page', function () {
    $this->get('/login')->assertOk();
});

it('redirects authenticated users away from login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
});

it('redirects guests from dashboard to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('authenticates with valid credentials', function () {
    $user = User::factory()->create();

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect('/dashboard');

    expect(auth()->check())->toBeTrue();
});

it('fails with invalid password', function () {
    $user = User::factory()->create();

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['email']);

    expect(auth()->check())->toBeFalse();
});

it('fails with non-email value', function () {
    Livewire::test('auth.login')
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email']);
});

it('requires email and password', function () {
    Livewire::test('auth.login')
        ->call('login')
        ->assertHasErrors(['email', 'password']);
});

it('logs out authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    expect(auth()->check())->toBeFalse();
});
