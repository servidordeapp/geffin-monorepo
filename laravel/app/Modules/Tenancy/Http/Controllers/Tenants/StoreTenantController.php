<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Http\Requests\StoreTenantRequest;
use App\Modules\Tenancy\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class StoreTenantController extends Controller
{
    public function __invoke(StoreTenantRequest $request, TenantProvisioningService $provisioner): RedirectResponse
    {
        try {
            /** @var array{name: string, slug: string, domain: string} $data */
            $data = $request->validated();
            $tenant = $provisioner(
                name: $data['name'],
                slug: $data['slug'],
                domain: $data['domain'],
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'provisioning' => $e->getMessage(),
            ]);
        }

        return redirect()->route('tenants.show', $tenant);
    }
}
