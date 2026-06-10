<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Tenant::class);

        return view('pages.tenants.index', [
            'tenants' => Tenant::with('domains')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Tenant::class);

        return view('pages.tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = Tenant::create(['name' => $request->validated('name')]);

        $tenant->domains()->create(['domain' => $request->validated('domain')]);

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant criado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant): View
    {
        Gate::authorize('update', $tenant);

        return view('pages.tenants.edit', [
            'tenant' => $tenant->load('domains'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update(['name' => $request->validated('name')]);

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant atualizado com sucesso.');
    }
}
