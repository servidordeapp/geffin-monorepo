<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Contracts\View\View;

class ShowTenantController extends Controller
{
    public function __invoke(Tenant $tenant): View
    {
        return view('tenancy.tenants.show', compact('tenant'));
    }
}
