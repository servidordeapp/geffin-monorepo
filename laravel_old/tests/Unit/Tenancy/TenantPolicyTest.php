<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenancy\Policies\TenantPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new TenantPolicy();
    $this->admin = User::factory()->create(['is_central_admin' => true]);
    $this->user = User::factory()->create(['is_central_admin' => false]);
});

it('returns true from before for central admins', function () {
    expect($this->policy->before($this->admin))->toBeTrue();
});

it('returns null from before for non-admin users', function () {
    expect($this->policy->before($this->user))->toBeNull();
});

it('denies viewAny for non-admin users', function () {
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

it('denies create for non-admin users', function () {
    expect($this->policy->create($this->user))->toBeFalse();
});

it('denies update for non-admin users', function () {
    expect($this->policy->update($this->user))->toBeFalse();
});

it('denies delete for non-admin users', function () {
    expect($this->policy->delete($this->user))->toBeFalse();
});

it('denies restore for non-admin users', function () {
    expect($this->policy->restore($this->user))->toBeFalse();
});

it('allows central admin via before for every ability', function () {
    foreach (['viewAny', 'create', 'update', 'delete', 'restore'] as $ability) {
        expect($this->admin->can($ability, \App\Modules\Tenancy\Models\Tenant::class))->toBeTrue();
    }
});

it('denies non-admin via Gate for every ability', function () {
    foreach (['viewAny', 'create', 'update', 'delete', 'restore'] as $ability) {
        expect($this->user->can($ability, \App\Modules\Tenancy\Models\Tenant::class))->toBeFalse();
    }
});
