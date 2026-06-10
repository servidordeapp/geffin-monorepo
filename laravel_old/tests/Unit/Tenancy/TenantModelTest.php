<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantAuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
});

it('persists slug, name, status, deleted_at as real columns', function () {
    $tenant = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'slug' => 'escola-um',
        'name' => 'Escola Um',
        'status' => 'active',
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->slug)->toBe('escola-um')
        ->and($fresh->name)->toBe('Escola Um')
        ->and($fresh->status->value)->toBe('active')
        ->and($fresh->deleted_at)->toBeNull();

    expect(array_key_exists('slug', $fresh->data ?? []))->toBeFalse();
    expect(array_key_exists('name', $fresh->data ?? []))->toBeFalse();
    expect(array_key_exists('status', $fresh->data ?? []))->toBeFalse();
    expect(array_key_exists('deleted_at', $fresh->data ?? []))->toBeFalse();
});

it('sets deleted_at via SoftDeletes', function () {
    $tenant = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'slug' => 'escola-dois',
        'name' => 'Escola Dois',
        'status' => 'active',
    ]);

    $tenant->delete();

    expect($tenant->deleted_at)->not()->toBeNull()
        ->and(Tenant::find($tenant->id))->toBeNull()
        ->and(Tenant::withTrashed()->find($tenant->id))->not()->toBeNull();
});

it('exposes its audit events through the auditEvents relation', function () {
    $tenant = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'slug' => 'escola-auditoria',
        'name' => 'Escola Auditoria',
        'status' => 'active',
    ]);

    TenantAuditEvent::create([
        'tenant_id' => $tenant->id,
        'actor_id' => null,
        'action' => 'created',
        'outcome' => 'success',
        'metadata' => ['foo' => 'bar'],
    ]);

    expect($tenant->auditEvents()->count())->toBe(1)
        ->and($tenant->auditEvents->first()->action)->toBe('created')
        ->and($tenant->auditEvents->first()->tenant_id)->toBe((string) $tenant->id);
});

it('refuses to hard-delete a tenant', function () {
    $tenant = Tenant::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'slug' => 'escola-imortal',
        'name' => 'Escola Imortal',
        'status' => 'active',
    ]);

    expect(fn () => $tenant->forceDelete())
        ->toThrow(\RuntimeException::class, 'Hard-delete of tenants is not permitted. Use soft-delete instead.');

    expect(Tenant::withTrashed()->find($tenant->id))->not()->toBeNull();
});

it('enforces slug uniqueness including trashed rows', function () {
    $id1 = \Illuminate\Support\Str::uuid();
    $id2 = \Illuminate\Support\Str::uuid();

    Tenant::create([
        'id' => $id1,
        'slug' => 'slug-unico',
        'name' => 'Escola A',
        'status' => 'active',
    ])->delete();

    expect(fn () => Tenant::create([
        'id' => $id2,
        'slug' => 'slug-unico',
        'name' => 'Escola B',
        'status' => 'active',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
