<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents cross-tenant data leakage (SC-002)', function () {
    $tenantA = Tenant::factory()->create(['slug' => 'escola-a', 'status' => 'active']);
    $tenantA->domains()->create(['domain' => 'escola-a.geffin.local']);

    $tenantB = Tenant::factory()->create(['slug' => 'escola-b', 'status' => 'active']);
    $tenantB->domains()->create(['domain' => 'escola-b.geffin.local']);

    // Write a marker row in tenant A context
    tenancy()->initialize($tenantA);
    \DB::table('users')->insert([
        'name' => 'Marker User A',
        'email' => 'marker-a@escola-a.local',
        'password' => 'hashed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    tenancy()->end();

    // Switch to tenant B and verify marker is NOT visible
    tenancy()->initialize($tenantB);
    $visibleFromB = \DB::table('users')->where('email', 'marker-a@escola-a.local')->exists();
    tenancy()->end();

    expect($visibleFromB)->toBeFalse();
});

it('provisions the sessions table in each tenant database', function () {
    $tenant = Tenant::factory()->create(['slug' => 'escola-sessao', 'status' => 'active']);

    tenancy()->initialize($tenant);
    $hasSessions = \Illuminate\Support\Facades\Schema::hasTable('sessions');
    tenancy()->end();

    expect($hasSessions)->toBeTrue();
});
