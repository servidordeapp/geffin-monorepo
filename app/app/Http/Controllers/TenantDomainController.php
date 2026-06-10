<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantDomainRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Stancl\Tenancy\Database\Models\Domain;

class TenantDomainController extends Controller
{
    /**
     * Add a domain to the tenant.
     */
    public function store(StoreTenantDomainRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->domains()->create(['domain' => $request->validated('domain')]);

        return redirect()
            ->route('tenants.edit', $tenant)
            ->with('status', 'Domínio adicionado com sucesso.');
    }

    /**
     * Remove a domain from the tenant.
     */
    public function destroy(Tenant $tenant, Domain $domain): RedirectResponse
    {
        Gate::authorize('update', $tenant);

        if ($tenant->domains()->count() <= 1) {
            return redirect()
                ->route('tenants.edit', $tenant)
                ->withErrors(['domain' => 'O tenant deve possuir pelo menos um domínio.']);
        }

        $domain->delete();

        return redirect()
            ->route('tenants.edit', $tenant)
            ->with('status', 'Domínio removido com sucesso.');
    }
}
