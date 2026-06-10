<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createActiveTenant(string $slug, string $domain): Tenant
{
    $tenant = Tenant::factory()->create(['slug' => $slug, 'status' => 'active']);
    $tenant->domains()->create(['domain' => $domain]);
    return $tenant;
}

it('returns 403 with contact-manager HTML for soft-deleted tenant', function () {
    $tenant = createActiveTenant('escola-del', 'escola-del.geffin.local');
    $tenant->delete();

    $response = $this->withoutVite()->get('http://escola-del.geffin.local/');

    $response->assertStatus(403);
    $response->assertSee('Indisponível', false);
});

it('returns 403 JSON for soft-deleted tenant when JSON requested', function () {
    $tenant = createActiveTenant('escola-json', 'escola-json.geffin.local');
    $tenant->delete();

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('http://escola-json.geffin.local/');

    $response->assertStatus(403);
    $response->assertJson(['error' => 'tenant_unavailable']);
});

it('returns 404 for unknown host', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('http://inexistente.geffin.local/');

    $response->assertStatus(404);
    $response->assertJson(['error' => 'tenant_not_found']);
});

it('preserves tenant DB and domain rows after soft-delete', function () {
    $tenant = createActiveTenant('escola-pres', 'escola-pres.geffin.local');
    $tenant->delete();

    expect(Tenant::withTrashed()->where('slug', 'escola-pres')->exists())->toBeTrue();
    expect(\App\Modules\Tenancy\Models\Domain::where('domain', 'escola-pres.geffin.local')->exists())->toBeTrue();
});

it('ignores stale auth credentials for trashed tenant', function () {
    $tenant = createActiveTenant('escola-stale', 'escola-stale.geffin.local');
    $tenant->delete();

    $response = $this->withHeaders([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer some-stale-token',
    ])->get('http://escola-stale.geffin.local/');

    $response->assertStatus(403);
    $response->assertJson(['error' => 'tenant_unavailable']);
});
