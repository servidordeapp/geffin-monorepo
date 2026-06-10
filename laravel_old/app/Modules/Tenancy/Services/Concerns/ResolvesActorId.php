<?php

declare(strict_types=1);

namespace App\Modules\Tenancy\Services\Concerns;

trait ResolvesActorId
{
    protected function actorId(): ?int
    {
        $id = auth()->id();

        return is_numeric($id) ? (int) $id : null;
    }
}
