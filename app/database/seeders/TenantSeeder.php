<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->domains()->create([
            'domain' => Str::slug($tenant->name) . '.localhost',
        ]);
    }
}
