<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->createOne([
            'name' => 'Admin',
            'email' => 'admin@geffin.com.br',
            'is_central_admin' => true,
        ]);
    }
}
