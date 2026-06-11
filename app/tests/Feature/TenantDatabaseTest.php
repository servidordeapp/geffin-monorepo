<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('tenant database is created with a sessions table', function () {
    $tenant = Tenant::factory()->create();

    try {
        $tenant->run(function () {
            expect(Schema::hasTable('sessions'))->toBeTrue();
        });
    } finally {
        $tenant->delete();
    }
});
