<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenant\Auth\Models\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('resolves the web guard to TenantUser inside a tenant context and reverts after', function () {
    $tenant = Tenant::factory()->create(['slug' => 'escola-auth', 'status' => 'active']);

    expect(config('auth.providers.users.model'))->toBe(User::class);

    tenancy()->initialize($tenant);

    expect(config('auth.providers.users.model'))->toBe(TenantUser::class);

    TenantUser::factory()->create(['email' => 'user@escola-auth.local']);

    expect(Auth::attempt(['email' => 'user@escola-auth.local', 'password' => 'password']))->toBeTrue()
        ->and(Auth::user())->toBeInstanceOf(TenantUser::class)
        ->and(Auth::user()->is_central_admin)->toBeFalse();

    tenancy()->end();

    expect(config('auth.providers.users.model'))->toBe(User::class);
});

it('maps TenantUser to the tenant users table without an is_central_admin column', function () {
    $tenant = Tenant::factory()->create(['slug' => 'escola-tabela', 'status' => 'active']);

    tenancy()->initialize($tenant);

    $user = TenantUser::factory()->create();

    expect($user->getTable())->toBe('users')
        ->and($user->fresh()->is_central_admin)->toBeFalse();

    tenancy()->end();
});
