<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TenantStatusEnum;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'   => fake()->company(),
            'status' => TenantStatusEnum::Ativo,
        ];
    }

    /**
     * Indicate that the tenant is suspended.
     */
    public function suspenso(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TenantStatusEnum::Suspenso,
        ]);
    }
}
