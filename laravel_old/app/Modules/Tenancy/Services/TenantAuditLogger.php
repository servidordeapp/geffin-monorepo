<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Models\TenantAuditEvent;

class TenantAuditLogger
{
    /** @param array<string, mixed>|null $metadata */
    public function log(
        TenantAuditActionEnum $action,
        string $outcome,
        ?string $tenantId = null,
        ?int $actorId = null,
        ?array $metadata = null,
    ): TenantAuditEvent {
        return TenantAuditEvent::create([
            'tenant_id' => $tenantId,
            'actor_id' => $actorId,
            'action' => $action->value,
            'outcome' => $outcome,
            'metadata' => $metadata,
        ]);
    }
}
