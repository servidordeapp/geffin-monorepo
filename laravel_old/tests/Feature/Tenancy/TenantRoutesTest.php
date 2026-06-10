<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantAuditEvent;
use App\Modules\Tenancy\Services\TenantProvisioningService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_central_admin' => true]);
    $this->actingAs($this->admin);
});

it('converts a provisioner exception into a provisioning validation error', function () {
    $this->mock(TenantProvisioningService::class, function ($mock) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->andThrow(new \RuntimeException('forced provisioning failure'));
    });

    $response = $this->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(route('tenants.store'), [
            'name' => 'Escola Erro',
            'slug' => 'escola-erro',
            'domain' => 'escola-erro.geffin.local',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['provisioning']);

    expect($response->json('errors.provisioning.0'))->toBe('forced provisioning failure');
});

it('renders the show view for an existing tenant', function () {
    $tenant = Tenant::factory()->create([
        'slug' => 'inquilino-show',
        'name' => 'Inquilino Show',
        'status' => 'active',
    ]);

    $response = $this->get(route('tenants.show', $tenant));

    $response->assertOk()
        ->assertViewIs('tenancy.tenants.show')
        ->assertViewHas('tenant', fn (Tenant $t) => $t->is($tenant));
});

it('renders the show view for a soft-deleted tenant via withTrashed binding', function () {
    $tenant = Tenant::factory()->create([
        'slug' => 'inquilino-show-del',
        'status' => 'active',
    ]);
    $tenant->delete();

    $response = $this->get(route('tenants.show', $tenant));

    $response->assertOk();
});

it('soft-deletes the tenant and redirects to index', function () {
    $tenant = Tenant::factory()->create([
        'slug' => 'inquilino-destruir',
        'status' => 'active',
    ]);

    $response = $this->withoutMiddleware(PreventRequestForgery::class)
        ->delete(route('tenants.destroy', $tenant));

    $response->assertRedirect(route('tenants.index'));

    expect(Tenant::withTrashed()->find($tenant->id)->trashed())->toBeTrue();

    expect(
        TenantAuditEvent::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', TenantAuditActionEnum::SoftDeleted->value)
            ->exists()
    )->toBeTrue();
});
