<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\Concerns\ResolvesActorId;

class RestoreTenantService
{
    use ResolvesActorId;

    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
    ) {
    }

    public function __invoke(Tenant $tenant): Tenant
    {
        $tenant->restore();
        $tenant->update(['status' => TenantStatusEnum::Active]);

        $this->auditLogger->log(
            TenantAuditActionEnum::Restored,
            'success',
            tenantId: $tenant->id,
            actorId: $this->actorId(),
        );

        return $tenant;
    }
}
