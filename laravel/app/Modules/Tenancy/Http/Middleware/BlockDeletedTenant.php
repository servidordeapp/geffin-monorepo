<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Middleware;

use App\Modules\Tenancy\Enums\TenantStatusEnum;
use App\Modules\Tenancy\Models\Domain;
use App\Modules\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDeletedTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        /** @var string[] $centralDomains */
        $centralDomains = config('tenancy.central_domains', []);
        if (in_array($host, $centralDomains, true)) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $domain = Domain::with('tenant')->where('domain', $host)->first();

        if ($domain === null) {
            return $this->notFoundResponse($request);
        }

        $tenant = $domain->tenant;

        if ($tenant instanceof Tenant) {
            if ($tenant->trashed()) {
                return $this->unavailableResponse($request);
            }

            $status = $tenant->status;
            if ($status === TenantStatusEnum::Pending || $status === TenantStatusEnum::Failed) {
                return $this->provisioningResponse($request, $status);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }

    private function unavailableResponse(Request $request): Response
    {
        $message = $this->resolveMessage('unavailable_message');
        $status = $this->intConfig('tenancy_block.status_unavailable', 403);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'tenant_unavailable',
                'message' => $message,
            ], $status);
        }

        return response()->view('tenancy.blocked', ['message' => $message], $status);
    }

    private function notFoundResponse(Request $request): Response
    {
        $message = $this->resolveMessage('not_found_message');
        $status = $this->intConfig('tenancy_block.status_not_found', 404);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'tenant_not_found',
                'message' => $message,
            ], $status);
        }

        return response()->view('tenancy.not-found', ['message' => $message], $status);
    }

    private function provisioningResponse(Request $request, TenantStatusEnum $TenantStatusEnum): Response
    {
        $isFailed = $TenantStatusEnum === TenantStatusEnum::Failed;
        $messageKey = $isFailed ? 'provisioning_failed_message' : 'provisioning_message';
        $errorCode = $isFailed ? 'tenant_provisioning_failed' : 'tenant_provisioning';
        $message = $this->resolveMessage($messageKey);
        $status = $this->intConfig('tenancy_block.status_provisioning', 503);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => $errorCode,
                'message' => $message,
            ], $status);
        }

        return response()->view('tenancy.provisioning', [
            'message' => $message,
            'failed' => $isFailed,
        ], $status);
    }

    private function resolveMessage(string $key): string
    {
        $value = config('tenancy_block.'.$key, 'tenancy.'.$key);

        return (string) __(is_string($value) ? $value : 'tenancy.'.$key);
    }

    private function intConfig(string $key, int $default): int
    {
        $value = config($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}
