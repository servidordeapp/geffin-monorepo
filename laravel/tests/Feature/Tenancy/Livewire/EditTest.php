<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Livewire\Tenants\Edit;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantAuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_central_admin' => true]);
    $this->tenant = Tenant::factory()->create([
        'slug' => 'inquilino-edicao',
        'name' => 'Nome Original',
        'status' => 'active',
    ]);
});

it('mounts with tenant name pre-filled', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->assertSet('name', 'Nome Original')
        ->assertSet('tenant.id', $this->tenant->id);
});

it('renders the tenancy.tenants.edit view', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->assertViewIs('tenancy.tenants.edit')
        ->assertViewHas('tenant', fn (Tenant $t) => $t->is($this->tenant));
});

it('forbids non-admin users via the manage-tenants gate', function () {
    $user = User::factory()->create(['is_central_admin' => false]);

    $this->actingAs($user)
        ->get(route('tenants.edit', $this->tenant))
        ->assertForbidden();
});

it('requires the name field', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('rejects a name longer than 255 characters', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->set('name', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

it('updates the tenant name and redirects to the show route', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->set('name', 'Nome Atualizado')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenants.show', $this->tenant));

    $this->tenant->refresh();

    expect($this->tenant->name)->toBe('Nome Atualizado')
        ->and($this->tenant->slug)->toBe('inquilino-edicao');
});

it('writes an audit event when the tenant is updated', function () {
    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['tenant' => $this->tenant])
        ->set('name', 'Nome Auditado')
        ->call('save');

    $event = TenantAuditEvent::query()
        ->where('tenant_id', $this->tenant->id)
        ->where('action', TenantAuditActionEnum::Updated->value)
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->actor_id)->toBe($this->admin->id);
});
