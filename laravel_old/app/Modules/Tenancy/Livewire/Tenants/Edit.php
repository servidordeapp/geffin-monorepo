<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Livewire\Tenants;

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\UpdateTenantService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Edit extends Component
{
    public Tenant $tenant;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    public function boot(): void
    {
        Gate::authorize('manage-tenants');
    }

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->name = $tenant->name;
    }

    public function save(UpdateTenantService $service): mixed
    {
        $this->validate();

        $service($this->tenant, ['name' => $this->name]);

        return $this->redirect(route('tenants.show', $this->tenant), navigate: true);
    }

    public function render(): View
    {
        return view('tenancy.tenants.edit', ['tenant' => $this->tenant]);
    }
}
