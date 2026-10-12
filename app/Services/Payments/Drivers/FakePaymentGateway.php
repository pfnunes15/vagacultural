<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Contracts\PaymentGateway;
use App\Models\TicketOrder;
use Illuminate\Support\Str;

/**
 * Test/dev gateway: mimics a provider without any external calls, so the full
 * payment lifecycle is exercisable before Stripe credentials exist.
 */
class FakePaymentGateway implements PaymentGateway
{
    /**
     * @return array{id: string, client_secret: string, status: string}
     */
    public function createIntent(TicketOrder $order): array
    {
        return [
            'id' => 'pi_fake_' . Str::lower(Str::random(20)),
            'client_secret' => 'cs_fake_' . Str::lower(Str::random(24)),
            'status' => 'requires_payment_method',
        ];
    }

    public function refund(TicketOrder $order): void
    {
        // no-op in the fake gateway
    }
}
