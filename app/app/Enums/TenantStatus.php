<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatus: string
{
    case Ativo = 'ativo';
    case Suspenso = 'suspenso';

    public function label(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Suspenso => 'Suspenso',
        };
    }
}
