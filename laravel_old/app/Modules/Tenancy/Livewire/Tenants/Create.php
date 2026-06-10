<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Livewire\Tenants;

use App\Modules\Tenancy\Http\Requests\StoreTenantRequest;
use App\Modules\Tenancy\Services\TenantProvisioningService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class Create extends Component
{
    #[Validate]
    public string $name = '';

    #[Validate]
    public string $slug = '';

    #[Validate]
    public string $domain = '';

    public ?string $errorMessage = null;

    public function boot(): void
    {
        Gate::authorize('manage-tenants');
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        $request = new StoreTenantRequest();

        return $request->rules();
    }

    public function save(TenantProvisioningService $provisioner): mixed
    {
        $this->validate();

        try {
            $provisioner(
                name: $this->name,
                slug: $this->slug,
                domain: $this->domain,
            );

            session()->flash('status', __('tenancy.provisioning_queued_notice'));

            return $this->redirect(route('tenants.index'), navigate: true);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }

        return null;
    }

    public function render(): View
    {
        return view('tenancy.tenants.create');
    }
}
