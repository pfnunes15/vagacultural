<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Free = 'free';
    case Pending = 'pending';
    case Paid = 'paid';
    case Expired = 'expired';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Grátis',
            self::Pending => 'Pagamento pendente',
            self::Paid => 'Pago',
            self::Expired => 'Expirado',
            self::Refunded => 'Reembolsado',
        };
    }
}
