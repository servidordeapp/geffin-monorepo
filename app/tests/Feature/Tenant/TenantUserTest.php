<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Tenant\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('tenant database is created with users and password reset tokens tables', function () {
    $tenant = Tenant::factory()->create();

    try {
        $tenant->run(function () {
            expect(Schema::hasTable('users'))->toBeTrue()
                ->and(Schema::hasTable('password_reset_tokens'))->toBeTrue();
        });
    } finally {
        $tenant->delete();
    }
});

test('a tenant user can be created inside the tenant context', function () {
    $tenant = Tenant::factory()->create();

    try {
        $tenant->run(function () {
            $user = TenantUser::factory()->create();

            expect($user->exists)->toBeTrue()
                ->and(TenantUser::count())->toBe(1);
        });

        expect(User::count())->toBe(0);
    } finally {
        $tenant->delete();
    }
});
