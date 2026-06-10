<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Stancl\Tenancy\Events\TenantCreated;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Prevent the tenancy pipeline from creating real tenant databases.
    Event::fake([TenantCreated::class]);

    $this->user = User::factory()->create();
});

test('authenticated users can list and create tenants', function (string $ability) {
    expect(Gate::forUser($this->user)->allows($ability, Tenant::class))->toBeTrue();
})->with(['viewAny', 'create']);

test('authenticated users can view and update a tenant', function (string $ability) {
    $tenant = Tenant::factory()->create();

    expect(Gate::forUser($this->user)->allows($ability, $tenant))->toBeTrue();
})->with(['view', 'update']);

test('tenants cannot be deleted, restored or force deleted', function (string $ability) {
    $tenant = Tenant::factory()->create();

    expect(Gate::forUser($this->user)->allows($ability, $tenant))->toBeFalse();
})->with(['delete', 'restore', 'forceDelete']);
