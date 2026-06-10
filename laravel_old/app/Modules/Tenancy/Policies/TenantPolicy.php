<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Policies;

use App\Models\User;

class TenantPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->is_central_admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function restore(User $user): bool
    {
        return false;
    }
}
