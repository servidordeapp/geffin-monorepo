<?php

namespace App\Modules\Administration\Providers;

use Illuminate\Support\ServiceProvider;

class AdministrationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/Administration'));
    }
}
