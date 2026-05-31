<?php

declare(strict_types=1);

use App\Modules\Tenancy\Providers\TenancyModuleServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\TenancyServiceProvider;
use Fruitcake\LaravelDebugbar\ServiceProvider;

return [
    AppServiceProvider::class,
    ServiceProvider::class,
    TenancyServiceProvider::class,
    TenancyModuleServiceProvider::class,
];
