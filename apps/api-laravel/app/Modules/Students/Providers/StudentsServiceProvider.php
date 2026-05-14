<?php

namespace App\Modules\Students\Providers;

use Illuminate\Support\ServiceProvider;

class StudentsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/Students'));
    }
}
