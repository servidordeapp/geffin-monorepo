<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\Concerns\ResolvesActorId;

class UpdateTenantService
{
    use ResolvesActorId;

    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function __invoke(Tenant $tenant, array $data): Tenant
    {
        $tenant->update(['name' => $data['name']]);

        $this->auditLogger->log(
            TenantAuditActionEnum::Updated,
            'success',
            tenantId: $tenant->id,
            actorId: $this->actorId(),
        );

        return $tenant;
    }
}
