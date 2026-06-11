<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatusEnum: string
{
    case Ativo    = 'ativo';
    case Suspenso = 'suspenso';

    public function label(): string
    {
        return match ($this) {
            self::Ativo    => 'Ativo',
            self::Suspenso => 'Suspenso',
        };
    }

    /**
     * CSS classes for the status badge.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Ativo    => 'badge badge-soft badge-success',
            self::Suspenso => 'badge badge-soft badge-warning',
        };
    }
}
