<?php

declare(strict_types=1);

namespace App\Enums;

enum OccurrenceStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case Postponed = 'postponed';
    case SoldOut = 'sold_out';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Agendado',
            self::Cancelled => 'Cancelado',
            self::Postponed => 'Adiado',
            self::SoldOut => 'Esgotado',
        };
    }
}
