<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\SoftDeleteTenantService;
use Illuminate\Http\RedirectResponse;

class DestroyTenantController extends Controller
{
    public function __invoke(Tenant $tenant, SoftDeleteTenantService $service): RedirectResponse
    {
        $service($tenant);

        return redirect()->route('tenants.index');
    }
}
