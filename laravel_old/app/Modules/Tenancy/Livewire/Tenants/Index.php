<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Livewire\Tenants;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url(as: 'incluir_excluidos')]
    public bool $incluirExcluidos = false;

    public function boot(): void
    {
        Gate::authorize('manage-tenants');
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    /** @return array{total: int, active: int, deleted: int} */
    protected function stats(): array
    {
        return [
            'total'   => Tenant::withTrashed()->count(),
            'active'  => Tenant::query()->count(),
            'deleted' => Tenant::onlyTrashed()->count(),
        ];
    }

    /** @return LengthAwarePaginator<int, Tenant> */
    protected function tenants(): LengthAwarePaginator
    {
        $query = Tenant::withCount('domains')
            ->with(['domains' => function (\Illuminate\Database\Eloquent\Relations\Relation $relation) {
                $relation->orderBy('id');
            }]);

        if ($this->incluirExcluidos) {
            $query->withTrashed();
        }

        if ($this->q !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->q.'%')
                    ->orWhere('slug', 'like', '%'.$this->q.'%')
                    ->orWhereHas('domains', fn ($d) => $d->where('domain', 'like', '%'.$this->q.'%'));
            });
        }

        return $query->latest()->paginate(20);
    }

    public function render(): View
    {
        return view('tenancy.tenants.index', [
            'tenants' => $this->tenants(),
            'stats'   => $this->stats(),
        ]);
    }
}
