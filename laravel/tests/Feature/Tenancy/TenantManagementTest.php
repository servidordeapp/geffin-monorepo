<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_central_admin' => true]);
    $this->actingAs($this->admin);

});

it('lists active tenants by default', function () {
    $active = Tenant::factory()->create(['slug' => 'ativo', 'status' => 'active', 'name' => 'Ativo Corp']);
    $trashed = Tenant::factory()->create(['slug' => 'excluido', 'status' => 'active', 'name' => 'Excluido Corp']);
    $trashed->delete();

    $response = $this->get(route('tenants.index'));

    $response->assertOk();
    $response->assertSee('Ativo Corp');
    $response->assertDontSee('Excluido Corp');
});

it('does not inject the flux appearance script that flips ghost buttons to white', function () {
    // @fluxAppearance adds the `dark` class to <html> on dark-OS reloads; the UI is
    // light-only, so that would render ghost action buttons as white-on-light (invisible).
    Tenant::factory()->create(['slug' => 'visivel', 'status' => 'active', 'name' => 'Visivel Corp']);

    $response = $this->get(route('tenants.index'));

    $response->assertOk();
    $response->assertDontSee('applyAppearance', false);
});

it('includes trashed when incluir_excluidos=1', function () {
    $trashed = Tenant::factory()->create(['slug' => 'excluido2', 'status' => 'active', 'name' => 'Excluido Dois']);
    $trashed->delete();

    $response = $this->get(route('tenants.index').'?incluir_excluidos=1');

    $response->assertOk();
    $response->assertSee('Excluido Dois');
});

it('updates tenant name without changing slug or DB', function () {
    $tenant = Tenant::factory()->create(['slug' => 'slug-fixo', 'name' => 'Nome Antigo', 'status' => 'active']);
    $originalSlug = $tenant->slug;

    $response = $this->patchJson(route('tenants.update', $tenant), ['name' => 'Nome Novo']);

    $response->assertRedirect();

    $tenant->refresh();
    expect($tenant->name)->toBe('Nome Novo')
        ->and($tenant->slug)->toBe($originalSlug);
});

it('rejects slug collision with trashed tenant on update', function () {
    $existingTrashed = Tenant::factory()->create(['slug' => 'slug-colisao', 'status' => 'active']);
    $existingTrashed->delete();

    $editTarget = Tenant::factory()->create(['slug' => 'slug-alvo', 'status' => 'active']);

    $response = $this->patchJson(route('tenants.update', $editTarget), [
        'name' => 'Novo Nome',
        'slug' => 'slug-colisao',
    ]);

    $response->assertUnprocessable();

    $editTarget->refresh();
    expect($editTarget->slug)->toBe('slug-alvo');
});
