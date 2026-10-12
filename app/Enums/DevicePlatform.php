<?php

declare(strict_types=1);

namespace App\Enums;

enum DevicePlatform: string
{
    case Ios = 'ios';
    case Android = 'android';

    public function label(): string
    {
        return match ($this) {
            self::Ios => 'iOS',
            self::Android => 'Android',
        };
    }
}
