<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Tenant\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

test('tenant login screen can be rendered on the tenant domain', function () {
    $this->get('http://acme.localhost/logar')
        ->assertOk()
        ->assertSeeLivewire('pages::tenant.auth.login');
});

test('tenant dashboard redirects guests to the tenant login', function () {
    $this->get('http://acme.localhost/painel')
        ->assertRedirect(route('tenant.login'));
});

test('tenant users can authenticate with valid credentials', function () {
    tenancy()->initialize($this->tenant);

    $user = TenantUser::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    Livewire::test('pages::tenant.auth.login')
        ->set('email', $user->email)
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenant.dashboard'));

    $this->assertAuthenticatedAs($user, 'tenant');
});

test('tenant users can not authenticate with an invalid password', function () {
    tenancy()->initialize($this->tenant);

    $user = TenantUser::factory()->create();

    Livewire::test('pages::tenant.auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertNoRedirect();

    expect(auth()->guard('tenant')->check())->toBeFalse();
});

test('central users can not authenticate on the tenant login', function () {
    $central = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.login')
        ->set('email', $central->email)
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertNoRedirect();

    expect(auth()->guard('tenant')->check())->toBeFalse();
});
