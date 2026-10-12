<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\TicketOrder;
use App\Services\Ticketing\PaymentService;
use Illuminate\Console\Command;

class ExpireTicketHolds extends Command
{
    protected $signature = 'tickets:expire-holds';

    protected $description = 'Release inventory from unpaid ticket orders whose hold has expired';

    public function handle(PaymentService $payments): int
    {
        $expired = 0;

        TicketOrder::query()
            ->where('payment_status', PaymentStatus::Pending->value)
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->chunkById(200, function ($orders) use ($payments, &$expired): void {
                foreach ($orders as $order) {
                    $payments->expire($order);
                    $expired++;
                }
            });

        $this->info("Expired {$expired} unpaid order(s).");

        return self::SUCCESS;
    }
}
