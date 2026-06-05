<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class TenantSeeder extends Seeder
{
    /**
     * Replicates the stancl/tenancy quickstart:
     *
     *   $tenant1 = Tenant::create(['id' => 'foo']);
     *   $tenant1->domains()->create(['domain' => 'foo.localhost']);
     *
     * Each tenant's database is provisioned + migrated synchronously so the
     * seeded tenants are immediately usable (production provisioning runs
     * through the queued ProvisionTenantDatabase job instead).
     */
    public function run(): void
    {
        $tenants = [
            ['id' => 'foo', 'domain' => 'foo.localhost'],
            ['id' => 'bar', 'domain' => 'bar.localhost'],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::create([
                'id' => $data['id'],
                'slug' => $data['id'],
                'name' => ucfirst($data['id']),
                'status' => TenantStatusEnum::Active->value,
            ]);

            $tenant->domains()->create(['domain' => $data['domain']]);

            $manager = $tenant->database()->manager();

            // Drop any stale tenant DB left over from a previous run so the
            // schema is always rebuilt fresh (central migrate:fresh does not
            // touch the physical tenant databases).
            if ($manager->databaseExists((string) $tenant->database()->getName())) {
                $manager->deleteDatabase($tenant);
            }

            $manager->createDatabase($tenant);
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            $tenant->run(function () use ($data): void {
                User::factory()->create([
                    'email' => 'user@'.$data['domain'],
                ]);
            });
        }
    }
}
