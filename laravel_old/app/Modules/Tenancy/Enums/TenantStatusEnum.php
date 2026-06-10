<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Enums;

enum TenantStatusEnum: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';
    case Failed = 'failed';
}
