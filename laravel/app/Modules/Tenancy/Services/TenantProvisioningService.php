<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Jobs\ProvisionTenantDatabase;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\Concerns\ResolvesActorId;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantProvisioningService
{
    use ResolvesActorId;

    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
    ) {
    }

    /**
     * Persist the central tenant + domain rows synchronously (so unique
     * constraints fail fast for the admin), then dispatch the database
     * creation + migration as a queued job. The returned tenant is in
     * `pending` status until the queue worker finishes provisioning.
     */
    public function __invoke(string $name, string $slug, string $domain): Tenant
    {
        $actorId = $this->actorId();

        try {
            $tenant = DB::transaction(function () use ($name, $slug, $domain) {
                $tenant = new Tenant();
                $tenant->fill([
                    'name' => $name,
                    'slug' => $slug,
                    'status' => TenantStatusEnum::Pending->value,
                ]);
                $tenant->save();

                $tenant->domains()->create(['domain' => $domain]);

                return $tenant;
            });
        } catch (Throwable $e) {
            $this->auditLogger->log(
                TenantAuditActionEnum::ProvisionFailed,
                'failure',
                actorId: $actorId,
                metadata: ['error_class' => get_class($e), 'error' => $e->getMessage()],
            );

            throw $e;
        }

        $this->auditLogger->log(
            TenantAuditActionEnum::ProvisionQueued,
            'success',
            tenantId: $tenant->id,
            actorId: $actorId,
        );

        app(Dispatcher::class)->dispatch(new ProvisionTenantDatabase((string) $tenant->id, $actorId));

        return $tenant;
    }
}
