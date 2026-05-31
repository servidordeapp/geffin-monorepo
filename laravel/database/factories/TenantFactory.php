<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Throwable;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'id' => Str::uuid()->toString(),
            'slug' => $slug,
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'status' => 'active',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant): void {
            // Provision tenant DB synchronously for tests — production-side
            // provisioning runs through the queued ProvisionTenantDatabase job.
            try {
                if (! $tenant->database()->manager()->databaseExists((string) $tenant->database()->getName())) {
                    $tenant->database()->manager()->createDatabase($tenant);
                    Artisan::call('tenants:migrate', [
                        '--tenants' => [$tenant->id],
                        '--force' => true,
                    ]);
                }
            } catch (Throwable) {
            }
        });
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
