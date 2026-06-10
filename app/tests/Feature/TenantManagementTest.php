<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Prevent the tenancy pipeline from creating real tenant databases.
    Event::fake([TenantCreated::class]);

    $this->user = User::factory()->create();
});

test('guests are redirected to the login screen', function () {
    $this->get(route('tenants.index'))
        ->assertRedirect(route('login'));
});

test('tenant list shows existing tenants and their domains', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);
    $tenant->domains()->create(['domain' => 'alfa2.localhost']);

    $this->actingAs($this->user)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertSee('Escola Alfa')
        ->assertSee('alfa.localhost')
        ->assertSee('alfa2.localhost');
});

test('create form can be rendered', function () {
    $this->actingAs($this->user)
        ->get(route('tenants.create'))
        ->assertOk();
});

test('a tenant can be created with a domain', function () {
    $this->actingAs($this->user)
        ->post(route('tenants.store'), [
            'name'   => 'Escola Beta',
            'domain' => 'beta.localhost',
        ])
        ->assertRedirect(route('tenants.index'));

    $tenant = Tenant::all()->firstWhere('name', 'Escola Beta');

    expect($tenant)->not->toBeNull();
    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenant->id,
        'domain'    => 'beta.localhost',
    ]);
});

test('name and domain are required to create a tenant', function () {
    $this->actingAs($this->user)
        ->post(route('tenants.store'), [])
        ->assertSessionHasErrors(['name', 'domain']);

    expect(Tenant::count())->toBe(0);
});

test('domain must be unique to create a tenant', function () {
    Tenant::factory()->create()->domains()->create(['domain' => 'taken.localhost']);

    $this->actingAs($this->user)
        ->post(route('tenants.store'), [
            'name'   => 'Escola Gama',
            'domain' => 'taken.localhost',
        ])
        ->assertSessionHasErrors(['domain']);

    expect(Tenant::count())->toBe(1);
});

test('edit form can be rendered with the tenant domains', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);

    $this->actingAs($this->user)
        ->get(route('tenants.edit', $tenant))
        ->assertOk()
        ->assertSee('Escola Alfa')
        ->assertSee('alfa.localhost');
});

test('a tenant name can be updated', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);

    $this->actingAs($this->user)
        ->put(route('tenants.update', $tenant), [
            'name' => 'Escola Alfa Renomeada',
        ])
        ->assertRedirect(route('tenants.index'));

    expect($tenant->refresh()->name)->toBe('Escola Alfa Renomeada');
});

test('updating a tenant does not change its domains', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);

    $this->actingAs($this->user)
        ->put(route('tenants.update', $tenant), [
            'name'   => 'Escola Alfa Renomeada',
            'domain' => 'hacker.localhost',
        ])
        ->assertRedirect(route('tenants.index'));

    expect($tenant->domains()->pluck('domain')->all())->toBe(['alfa.localhost']);
});

test('a domain can be added to a tenant', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);

    $this->actingAs($this->user)
        ->post(route('tenants.domains.store', $tenant), [
            'domain' => 'alfa2.localhost',
        ])
        ->assertRedirect(route('tenants.edit', $tenant));

    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenant->id,
        'domain'    => 'alfa2.localhost',
    ]);
});

test('a domain already in use cannot be added', function () {
    Tenant::factory()->create()->domains()->create(['domain' => 'taken.localhost']);

    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);

    $this->actingAs($this->user)
        ->post(route('tenants.domains.store', $tenant), [
            'domain' => 'taken.localhost',
        ])
        ->assertSessionHasErrors(['domain']);

    expect($tenant->domains()->count())->toBe(0);
});

test('a domain can be removed from a tenant', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $tenant->domains()->create(['domain' => 'alfa.localhost']);
    $domain = $tenant->domains()->create(['domain' => 'alfa2.localhost']);

    $this->actingAs($this->user)
        ->delete(route('tenants.domains.destroy', [$tenant, $domain]))
        ->assertRedirect(route('tenants.edit', $tenant));

    $this->assertDatabaseMissing('domains', ['domain' => 'alfa2.localhost']);
});

test('the last domain of a tenant cannot be removed', function () {
    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);
    $domain = $tenant->domains()->create(['domain' => 'alfa.localhost']);

    $this->actingAs($this->user)
        ->delete(route('tenants.domains.destroy', [$tenant, $domain]))
        ->assertRedirect(route('tenants.edit', $tenant))
        ->assertSessionHasErrors(['domain']);

    $this->assertDatabaseHas('domains', ['domain' => 'alfa.localhost']);
});

test('a domain of another tenant cannot be removed', function () {
    $otherDomain = Tenant::factory()->create()->domains()->create(['domain' => 'other.localhost']);

    $tenant = Tenant::factory()->create(['name' => 'Escola Alfa']);

    $this->actingAs($this->user)
        ->delete(route('tenants.domains.destroy', [$tenant, $otherDomain]))
        ->assertNotFound();

    $this->assertDatabaseHas('domains', ['domain' => 'other.localhost']);
});
