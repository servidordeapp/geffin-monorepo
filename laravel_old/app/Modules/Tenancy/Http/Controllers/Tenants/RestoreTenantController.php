<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\RestoreTenantService;
use Illuminate\Http\RedirectResponse;

class RestoreTenantController extends Controller
{
    public function __invoke(Tenant $tenant, RestoreTenantService $service): RedirectResponse
    {
        $service($tenant);

        return redirect()->route('tenants.show', $tenant);
    }
}
