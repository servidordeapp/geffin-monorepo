<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeTenantWithDomain(string $slug, string $domain, string $status = 'active'): Tenant
{
    $tenant = Tenant::factory()->create(['slug' => $slug, 'status' => $status]);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('renders the tenancy.not-found view for unknown host on HTML request', function () {
    $response = $this->withoutVite()->get('http://desconhecido.geffin.local/');

    $response->assertStatus(404)
        ->assertViewIs('tenancy.not-found')
        ->assertSee(__('tenancy.not_found_message'), false);
});

it('returns 503 JSON for a pending tenant', function () {
    makeTenantWithDomain('inq-pending', 'inq-pending.geffin.local', 'pending');

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('http://inq-pending.geffin.local/');

    $response->assertStatus(503)
        ->assertJson([
            'error'   => 'tenant_provisioning',
            'message' => __('tenancy.provisioning_message'),
        ]);
});

it('renders the tenancy.provisioning view for a pending tenant on HTML request', function () {
    makeTenantWithDomain('inq-pending-html', 'inq-pending-html.geffin.local', 'pending');

    $response = $this->withoutVite()->get('http://inq-pending-html.geffin.local/');

    $response->assertStatus(503)
        ->assertViewIs('tenancy.provisioning')
        ->assertViewHas('failed', false)
        ->assertViewHas('message', __('tenancy.provisioning_message'));
});

it('returns 503 JSON with provisioning_failed code for a failed tenant', function () {
    makeTenantWithDomain('inq-falho', 'inq-falho.geffin.local', 'failed');

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('http://inq-falho.geffin.local/');

    $response->assertStatus(503)
        ->assertJson([
            'error'   => 'tenant_provisioning_failed',
            'message' => __('tenancy.provisioning_failed_message'),
        ]);
});

it('renders the tenancy.provisioning view with failed=true for a failed tenant', function () {
    makeTenantWithDomain('inq-falho-html', 'inq-falho-html.geffin.local', 'failed');

    $response = $this->withoutVite()->get('http://inq-falho-html.geffin.local/');

    $response->assertStatus(503)
        ->assertViewIs('tenancy.provisioning')
        ->assertViewHas('failed', true)
        ->assertViewHas('message', __('tenancy.provisioning_failed_message'));
});

it('passes through to the next middleware for an active tenant', function () {
    makeTenantWithDomain('inq-ativo', 'inq-ativo.geffin.local', 'active');

    $response = $this->withoutVite()->get('http://inq-ativo.geffin.local/');

    expect($response->status())->not->toBe(403)
        ->and($response->status())->not->toBe(404)
        ->and($response->status())->not->toBe(503);
});
