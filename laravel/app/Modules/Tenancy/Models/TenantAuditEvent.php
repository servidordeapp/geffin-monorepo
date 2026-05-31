<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAuditEvent extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'actor_id',
        'action',
        'outcome',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
