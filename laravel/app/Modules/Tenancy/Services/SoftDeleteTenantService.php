<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\Concerns\ResolvesActorId;
use RuntimeException;

class SoftDeleteTenantService
{
    use ResolvesActorId;

    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
    ) {
    }

    public function __invoke(Tenant $tenant): void
    {
        $current = tenancy()->tenant;
        $currentId = $current?->getAttribute('id');
        if (tenancy()->initialized && is_scalar($currentId) && (string) $currentId === (string) $tenant->id) {
            throw new RuntimeException('Cannot soft-delete a tenant from within a tenant context.');
        }

        $centralDomains = (array) config('tenancy.central_domains', []);
        if (in_array($tenant->id, $centralDomains, strict: true)) {
            throw new RuntimeException('Cannot soft-delete the central tenant.');
        }

        $tenant->update(['status' => TenantStatusEnum::Inactive]);
        $tenant->delete();

        $this->auditLogger->log(
            TenantAuditActionEnum::SoftDeleted,
            'success',
            tenantId: $tenant->id,
            actorId: $this->actorId(),
        );
    }
}
