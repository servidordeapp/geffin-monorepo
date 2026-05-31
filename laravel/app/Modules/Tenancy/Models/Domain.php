<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    /**
     * Absolute URL to the tenant site for this domain.
     *
     * Scheme and port are taken from the central app's APP_URL, since tenant
     * domains are served by the same deployment (e.g. http on :8080 in dev,
     * https with no explicit port in production).
     */
    public function url(): string
    {
        $host = (string) $this->domain;

        if (str_contains($host, '://')) {
            return $host;
        }

        $appUrl = config('app.url');
        $base = parse_url(is_string($appUrl) ? $appUrl : '') ?: [];
        $scheme = $base['scheme'] ?? 'https';
        $port = $base['port'] ?? null;
        $suffix = ($port !== null && ! str_contains($host, ':')) ? ':'.$port : '';

        return $scheme.'://'.$host.$suffix;
    }

    protected static function newFactory(): DomainFactory
    {
        return DomainFactory::new();
    }
}
