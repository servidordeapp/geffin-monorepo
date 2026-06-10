<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyCentralDomains
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string[] $centralDomains */
        $centralDomains = config('tenancy.central_domains', []);

        if (! in_array($request->getHost(), $centralDomains, true)) {
            abort(404);
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
