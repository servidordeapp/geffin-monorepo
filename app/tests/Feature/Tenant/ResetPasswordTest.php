<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Tenant\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->tenant->domains()->create(['domain' => 'acme.localhost']);
});

afterEach(function () {
    tenancy()->end();
    $this->tenant->delete();
});

test('reset password screen can be rendered on the tenant domain', function () {
    $this->get('http://acme.localhost/redefinir-senha/fake-token')
        ->assertOk()
        ->assertSeeLivewire('pages::tenant.auth.reset-password');
});

test('email from the query string prefills the form', function () {
    $this->get('http://acme.localhost/redefinir-senha/fake-token?email=user@school.edu.br')
        ->assertOk()
        ->assertSee('user@school.edu.br');
});

test('email is required', function () {
    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.reset-password', ['token' => 'fake-token'])
        ->set('email', '')
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasErrors(['email' => 'required']);
});

test('password is required', function () {
    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.reset-password', ['token' => 'fake-token'])
        ->set('email', 'user@school.edu.br')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be confirmed', function () {
    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.reset-password', ['token' => 'fake-token'])
        ->set('email', 'user@school.edu.br')
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'different-password')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('password can not be reset with an invalid token', function () {
    tenancy()->initialize($this->tenant);

    $user = TenantUser::factory()->create();

    Livewire::test('pages::tenant.auth.reset-password', ['token' => 'invalid-token'])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasErrors('email')
        ->assertNoRedirect();
});

test('password can be reset with a valid token', function () {
    tenancy()->initialize($this->tenant);

    $user  = TenantUser::factory()->create();
    $token = Password::broker('tenant_users')->createToken($user);

    Livewire::test('pages::tenant.auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenant.login'));

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

test('tenant user can authenticate with the new password after reset', function () {
    tenancy()->initialize($this->tenant);

    $user  = TenantUser::factory()->create();
    $token = Password::broker('tenant_users')->createToken($user);

    Livewire::test('pages::tenant.auth.reset-password', ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->set('password_confirmation', 'new-secret-password')
        ->call('resetPassword');

    Livewire::test('pages::tenant.auth.login')
        ->set('email', $user->email)
        ->set('password', 'new-secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenant.dashboard'));

    $this->assertAuthenticatedAs($user, 'tenant');
});
