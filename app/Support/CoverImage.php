<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Event cover image must match Instagram portrait (4:5) exactly.
 */
final class CoverImage
{
    public const WIDTH = 1080;

    public const HEIGHT = 1350;

    public const RATIO = '4:5';
}
