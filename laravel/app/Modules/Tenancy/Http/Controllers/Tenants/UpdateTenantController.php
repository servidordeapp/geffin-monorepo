<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Http\Requests\UpdateTenantRequest;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\UpdateTenantService;
use Illuminate\Http\RedirectResponse;

class UpdateTenantController extends Controller
{
    public function __invoke(UpdateTenantRequest $request, Tenant $tenant, UpdateTenantService $service): RedirectResponse
    {
        $service($tenant, $request->validated());

        return redirect()->route('tenants.show', $tenant);
    }
}
