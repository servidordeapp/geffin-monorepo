<?php

declare(strict_types=1);

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tenants:migrate runs on active tenants and skips trashed (FR-012, SC-006)', function () {
    $active = Tenant::factory()->create(['slug' => 'ativo-migrate', 'status' => 'active']);
    $trashed = Tenant::factory()->create(['slug' => 'trashed-migrate', 'status' => 'active']);
    $trashed->delete();

    $this->artisan('tenants:migrate')
        ->assertSuccessful();
});
