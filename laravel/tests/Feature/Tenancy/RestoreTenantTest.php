<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_central_admin' => true]);
    $this->actingAs($this->admin);
    $this->withoutMiddleware(VerifyCsrfToken::class);
});

it('restores a trashed tenant: clears deleted_at, sets status active', function () {
    $tenant = Tenant::factory()->create(['slug' => 'escola-rest', 'status' => 'active']);
    $tenant->delete();

    $response = $this->post(route('tenants.restore', $tenant));

    $response->assertRedirect();

    $tenant->refresh();
    expect($tenant->deleted_at)->toBeNull()
        ->and($tenant->status)->toBe(TenantStatusEnum::Active);
});

it('writes a restored audit event', function () {
    $tenant = Tenant::factory()->create(['slug' => 'escola-audit', 'status' => 'active']);
    $tenant->delete();

    $this->post(route('tenants.restore', $tenant));

    expect(
        \DB::table('tenant_audit_events')
        ->where('tenant_id', $tenant->id)
        ->where('action', 'restored')
        ->where('outcome', 'success')
        ->exists()
    )->toBeTrue();
});
