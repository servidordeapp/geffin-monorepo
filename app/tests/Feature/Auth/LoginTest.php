<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeLivewire('pages::auth.login');
});

test('email is required', function () {
    Livewire::test('pages::auth.login')
        ->set('email', '')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email' => 'required'])
        ->assertNoRedirect();

    expect(auth()->check())->toBeFalse();
});

test('email must be a valid address', function () {
    Livewire::test('pages::auth.login')
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email' => 'email']);
});

test('password is required', function () {
    Livewire::test('pages::auth.login')
        ->set('email', 'user@school.edu.br')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors(['password' => 'required']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertNoRedirect();

    expect(auth()->check())->toBeFalse();
});

test('users can not authenticate with unknown email', function () {
    Livewire::test('pages::auth.login')
        ->set('email', 'ghost@school.edu.br')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

test('users can authenticate with valid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('remember me persists the session', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret-password')
        ->set('remember', true)
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->remember_token)->not->toBeNull();
});
