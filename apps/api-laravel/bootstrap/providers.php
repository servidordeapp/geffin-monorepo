<?php

use App\Modules\Administration\Providers\AdministrationServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Students\Providers\StudentsServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    AdministrationServiceProvider::class,
    StudentsServiceProvider::class,
];
