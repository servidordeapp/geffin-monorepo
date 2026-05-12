<?php

namespace Database\Seeders;

use App\Modules\Administration\Models\SchoolAdmin;
use App\Modules\Students\Models\Guardian;
use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        Guardian::create([
            'name' => 'Test Guardian',
            'email' => 'guardian@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => true,
        ]);

        Guardian::create([
            'name' => 'Unverified Guardian',
            'email' => 'unverified@test.com',
            'password' => 'password',
            'email_verified_at' => null,
            'active' => true,
        ]);

        SchoolAdmin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'email_verified_at' => now(),
            'active' => true,
        ]);
    }
}
