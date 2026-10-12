<?php

declare(strict_types=1);

namespace App\Services\Ticketing;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * Renders a ticket code as a QR image (SVG — crisp at any size, no GD needed).
 */
class QrCodeGenerator
{
    public function svg(string $data): string
    {
        return (new SvgWriter)->write(new QrCode($data))->getString();
    }
}
