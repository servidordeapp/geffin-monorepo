<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Jobs;

use App\Modules\Tenancy\Enums\TenantAuditActionEnum;
use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantAuditLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Throwable;

class ProvisionTenantDatabase implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        public readonly string $tenantId,
        public readonly ?int $actorId = null,
    ) {
    }

    public function handle(TenantAuditLogger $auditLogger): void
    {
        $tenant = Tenant::withTrashed()->find($this->tenantId);

        if ($tenant === null) {
            return;
        }

        try {
            app(CreateDatabase::class, ['tenant' => $tenant])->handle(app(\Stancl\Tenancy\Database\DatabaseManager::class));
            app(MigrateDatabase::class, ['tenant' => $tenant])->handle();

            $tenant->status = TenantStatusEnum::Active;
            $tenant->save();

            $auditLogger->log(
                TenantAuditActionEnum::Created,
                'success',
                tenantId: $tenant->id,
                actorId: $this->actorId,
            );
        } catch (Throwable $e) {
            // Swallow inside the job: sync queue would re-throw and the bus
            // dispatcher would propagate the exception back to the HTTP
            // request, defeating the async contract. The failure is recorded
            // in tenant_audit_events and surfaced via Tenant.status=failed.
            $this->markFailed($tenant, $auditLogger, $e);
        }
    }

    private function markFailed(Tenant $tenant, TenantAuditLogger $auditLogger, Throwable $e): void
    {
        $this->cleanupPartialTenant($tenant);

        $auditLogger->log(
            TenantAuditActionEnum::ProvisionFailed,
            'failure',
            tenantId: $tenant->id,
            actorId: $this->actorId,
            metadata: ['error_class' => get_class($e), 'error' => $e->getMessage()],
        );
    }

    private function cleanupPartialTenant(Tenant $tenant): void
    {
        try {
            if ($tenant->database()->manager()->databaseExists((string) $tenant->database()->getName())) {
                $tenant->database()->manager()->deleteDatabase($tenant);
            }
        } catch (Throwable) {
        }

        try {
            $tenant->domains()->delete();
        } catch (Throwable) {
        }

        try {
            Tenant::withTrashed()->whereKey($tenant->getKey())->forceDelete();
        } catch (Throwable) {
        }
    }
}
