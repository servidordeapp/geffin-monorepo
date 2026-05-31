<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Jobs\ProvisionTenantDatabase;
use App\Modules\Tenancy\Livewire\Tenants\Create;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_central_admin' => true]);
});

it('renders the tenancy.tenants.create view', function () {
    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->assertViewIs('tenancy.tenants.create');
});

it('forbids non-admin users via the manage-tenants gate', function () {
    $user = User::factory()->create(['is_central_admin' => false]);

    $this->actingAs($user)
        ->get(route('tenants.create'))
        ->assertForbidden();
});

it('pulls validation rules from StoreTenantRequest', function () {
    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->set('name', '')
        ->set('slug', '')
        ->set('domain', '')
        ->call('save')
        ->assertHasErrors([
            'name'   => 'required',
            'slug'   => 'required',
            'domain' => 'required',
        ]);
});

it('rejects an invalid slug format', function () {
    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->set('name', 'Inquilino X')
        ->set('slug', 'Slug Invalido!')
        ->set('domain', 'inq.geffin.local')
        ->call('save')
        ->assertHasErrors(['slug']);
});

it('provisions a tenant and redirects to index on success', function () {
    $tenant = Tenant::factory()->make(['slug' => 'inquilino-novo']);

    $mock = $this->mock(TenantProvisioningService::class);
    $mock->shouldReceive('__invoke')
        ->once()
        ->withArgs(function (string $name, string $slug, string $domain) {
            return $name === 'Inquilino Novo'
                && $slug === 'inquilino-novo'
                && $domain === 'novo.geffin.local';
        })
        ->andReturn($tenant);

    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->set('name', 'Inquilino Novo')
        ->set('slug', 'inquilino-novo')
        ->set('domain', 'novo.geffin.local')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('tenants.index'));

    expect(session('status'))->toBe(__('tenancy.provisioning_queued_notice'));
});

it('captures provisioning failures into errorMessage without redirecting', function () {
    $mock = $this->mock(TenantProvisioningService::class);
    $mock->shouldReceive('__invoke')
        ->once()
        ->andThrow(new \RuntimeException('forced failure'));

    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->set('name', 'Inquilino Falho')
        ->set('slug', 'inquilino-falho')
        ->set('domain', 'falho.geffin.local')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNoRedirect()
        ->assertSet('errorMessage', 'forced failure');
});

it('dispatches the provisioning job with the authenticated user as actor', function () {
    Queue::fake();

    Livewire::actingAs($this->admin)
        ->test(Create::class)
        ->set('name', 'Inquilino Auth')
        ->set('slug', 'inquilino-auth')
        ->set('domain', 'auth.geffin.local')
        ->call('save');

    Queue::assertPushed(
        ProvisionTenantDatabase::class,
        fn (ProvisionTenantDatabase $job) => $job->actorId === $this->admin->id,
    );
});
