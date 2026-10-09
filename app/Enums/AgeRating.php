<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Minimum-age rating for an event (European numeric scale).
 */
enum AgeRating: int
{
    case All = 0;
    case ThreePlus = 3;
    case SixPlus = 6;
    case TwelvePlus = 12;
    case FourteenPlus = 14;
    case SixteenPlus = 16;
    case EighteenPlus = 18;

    public function label(): string
    {
        return $this === self::All ? 'Todos' : $this->value . '+';
    }

    /**
     * @return list<int>
     */
    public static function values(): array
    {
        return array_map(fn (self $c): int => $c->value, self::cases());
    }
}
