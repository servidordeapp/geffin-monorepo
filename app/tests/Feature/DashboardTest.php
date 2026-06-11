<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard screen can be rendered by an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});

test('dashboard shows the number of registered tenants', function () {
    Tenant::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Tenants cadastrados')
        ->assertSeeText('3');
});

test('guests are redirected to the login screen', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
