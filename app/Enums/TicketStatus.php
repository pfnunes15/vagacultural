<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Valid = 'valid';
    case Used = 'used';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Válido',
            self::Used => 'Utilizado',
            self::Cancelled => 'Cancelado',
        };
    }
}
