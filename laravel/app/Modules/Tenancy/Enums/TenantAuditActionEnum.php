<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Enums;

enum TenantAuditActionEnum: string
{
    case Created = 'created';
    case ProvisionQueued = 'provision_queued';
    case Updated = 'updated';
    case SoftDeleted = 'soft_deleted';
    case Restored = 'restored';
    case Migrated = 'migrated';
    case ProvisionFailed = 'provision_failed';
}
