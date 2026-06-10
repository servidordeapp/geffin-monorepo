<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Enums\TenantStatusEnum;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property TenantStatusEnum $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;
    use HasDatabase;
    use SoftDeletes;

    /** @return array<string> */
    public static function getCustomColumns(): array
    {
        return ['id', 'slug', 'name', 'status', 'deleted_at'];
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => TenantStatusEnum::class,
        ];
    }

    /** @return HasMany<Domain, $this> */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /** @return HasMany<TenantAuditEvent, $this> */
    public function auditEvents(): HasMany
    {
        return $this->hasMany(TenantAuditEvent::class, 'tenant_id');
    }

    public function forceDelete(): ?bool
    {
        throw new \RuntimeException('Hard-delete of tenants is not permitted. Use soft-delete instead.');
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
