<?php

namespace Database\Factories;

use App\Modules\Administration\Models\SchoolAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolAdminFactory extends Factory
{
    protected $model = SchoolAdmin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
