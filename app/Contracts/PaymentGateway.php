<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\TicketOrder;

/**
 * Payment gateway abstraction (Stripe Connect in production).
 *
 * @phpstan-type Intent array{id: string, client_secret: string, status: string}
 */
interface PaymentGateway
{
    /**
     * Create a payment intent for an order, retaining the platform commission
     * (application fee) and routing funds to the promoter's connected account.
     *
     * @return Intent
     */
    public function createIntent(TicketOrder $order): array;

    /** Refund a paid order (reverses the application fee too). */
    public function refund(TicketOrder $order): void;
}
