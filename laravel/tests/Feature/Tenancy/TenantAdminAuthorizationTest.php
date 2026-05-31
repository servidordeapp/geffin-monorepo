<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guest to login', function () {
    $this->get(route('tenants.index'))->assertRedirect(route('login'));
    $this->get(route('tenants.create'))->assertRedirect(route('login'));
    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->post(route('tenants.store'), [])->assertRedirect(route('login'));
});

it('returns 403 for authenticated non-admin', function () {
    $user = User::factory()->create(['is_central_admin' => false]);
    $this->actingAs($user);

    $this->get(route('tenants.index'))->assertForbidden();
    $this->get(route('tenants.create'))->assertForbidden();
    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->post(route('tenants.store'), [])->assertForbidden();
});

it('allows central admin access', function () {
    $admin = User::factory()->create(['is_central_admin' => true]);
    $this->actingAs($admin);

    $this->get(route('tenants.index'))->assertOk();
    $this->get(route('tenants.create'))->assertOk();
});
