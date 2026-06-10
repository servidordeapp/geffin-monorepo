<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('reset password screen can be rendered', function () {
    $this->get(route('password.reset', ['token' => 'fake-token']))
        ->assertOk()
        ->assertSeeLivewire('pages::auth.reset-password');
});

test('email from the query string prefills the form', function () {
    $this->get(route('password.reset', ['token' => 'fake-token', 'email' => 'user@school.edu.br']))
        ->assertOk()
        ->assertSee('user@school.edu.br');
});

test('email is required', function () {
    Livewire::test('pages::auth.reset-password', ['token' => 'fake-token'])
        ->set('email', '')
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasErrors(['email' => 'required']);
});

test('password is required', function () {
    Livewire::test('pages::auth.reset-password', ['token' => 'fake-token'])
        ->set('email', 'user@school.edu.br')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be confirmed', function () {
    Livewire::test('pages::auth.reset-password', ['token' => 'fake-token'])
        ->set('email', 'user@school.edu.br')
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'different-password')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('password can not be reset with an invalid token', function () {
    $user = User::factory()->create();

    Livewire::test('pages::auth.reset-password', ['token' => 'invalid-token'])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasErrors('email')
        ->assertNoRedirect();
});

test('password can be reset with a valid token', function () {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test('pages::auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

test('user can authenticate with the new password after reset', function () {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test('pages::auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword');

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
