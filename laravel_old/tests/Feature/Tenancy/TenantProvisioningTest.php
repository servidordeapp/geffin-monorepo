<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Jobs\ProvisionTenantDatabase;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->centralAdmin = User::factory()->create(['is_central_admin' => true]);
    $this->actingAs($this->centralAdmin);

});

it('queues async provisioning: persists central rows in pending state, dispatches the job', function () {
    Queue::fake();

    $response = $this->postJson(route('tenants.store'), [
        'name' => 'Escola Um',
        'slug' => 'escola-um',
        'domain' => 'escola-um.geffin.local',
    ]);

    $tenant = Tenant::first();
    expect($tenant)->not()->toBeNull()
        ->and($tenant->slug)->toBe('escola-um')
        ->and($tenant->name)->toBe('Escola Um')
        ->and($tenant->status)->toBe(TenantStatusEnum::Pending)
        ->and($tenant->deleted_at)->toBeNull();

    $response->assertRedirect(route('tenants.show', $tenant));

    expect($tenant->domains()->where('domain', 'escola-um.geffin.local')->exists())->toBeTrue();

    Queue::assertPushed(ProvisionTenantDatabase::class, fn ($job) => $job->tenantId === $tenant->id);

    expect(
        \DB::table('tenant_audit_events')
        ->where('tenant_id', $tenant->id)
        ->where('action', 'provision_queued')
        ->where('outcome', 'success')
        ->exists()
    )->toBeTrue();
});

it('flips the tenant to active and writes the created audit row after the async job runs', function () {
    // QUEUE_CONNECTION=sync in phpunit.xml — dispatch runs the job inline.
    $response = $this->postJson(route('tenants.store'), [
        'name' => 'Escola Dois',
        'slug' => 'escola-dois',
        'domain' => 'escola-dois.geffin.local',
    ]);

    $tenant = Tenant::first();
    expect($tenant->status)->toBe(TenantStatusEnum::Active);

    $response->assertRedirect(route('tenants.show', $tenant));

    expect(
        \DB::table('tenant_audit_events')
        ->where('tenant_id', $tenant->id)
        ->where('action', 'created')
        ->where('outcome', 'success')
        ->exists()
    )->toBeTrue();
});

it('rolls back on async provisioning failure, writes provision_failed audit', function () {
    \Illuminate\Support\Facades\Event::listen(
        \Stancl\Tenancy\Events\CreatingDatabase::class,
        function () {
            throw new \RuntimeException('Forced DB creation failure');
        }
    );

    // Async contract: HTTP request returns the standard success redirect even
    // when the queued job ultimately fails. Failure is surfaced through
    // Tenant.status / tenant_audit_events, not through the HTTP response.
    $response = $this->postJson(route('tenants.store'), [
        'name' => 'Escola Falha',
        'slug' => 'escola-falha',
        'domain' => 'escola-falha.geffin.local',
    ]);

    $response->assertStatus(302);

    // sync queue ran the job inline → cleanup removed the central rows.
    expect(Tenant::withTrashed()->where('slug', 'escola-falha')->exists())->toBeFalse();
    expect(\App\Modules\Tenancy\Models\Domain::where('domain', 'escola-falha.geffin.local')->exists())->toBeFalse();

    expect(
        \DB::table('tenant_audit_events')
        ->where('action', 'provision_failed')
        ->where('outcome', 'failure')
        ->exists()
    )->toBeTrue();
});

it('rejects duplicate slug including trashed', function () {
    $existing = Tenant::factory()->create(['slug' => 'escola-dup', 'status' => 'active']);
    $existing->delete();

    $response = $this->postJson(route('tenants.store'), [
        'name' => 'Escola Dup Nova',
        'slug' => 'escola-dup',
        'domain' => 'nova.geffin.local',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['slug']);
});

it('rejects duplicate domain', function () {
    $tenant = Tenant::factory()->create(['slug' => 'existente', 'status' => 'active']);
    $tenant->domains()->create(['domain' => 'usado.geffin.local']);

    $response = $this->postJson(route('tenants.store'), [
        'name' => 'Escola X',
        'slug' => 'escola-x',
        'domain' => 'usado.geffin.local',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['domain']);
});
