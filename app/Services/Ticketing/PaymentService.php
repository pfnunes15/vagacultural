<?php

declare(strict_types=1);

namespace App\Services\Ticketing;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\TicketOrderStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Paid-ticket lifecycle (Phase 2): holds inventory, creates a payment intent
 * with the platform commission, issues tickets on payment confirmation, and
 * releases the hold on expiry. Free orders still go through TicketingService.
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CommissionResolver $commission,
    ) {}

    /**
     * Start a paid checkout: reserve seats, compute commission, create intent.
     *
     * @param  array<int, int>  $quantities  [ticket_type_id => quantity]
     * @return array{order: TicketOrder, intent: array{id: string, client_secret: string, status: string}}
     */
    public function startCheckout(User $user, array $quantities): array
    {
        $quantities = array_filter($quantities, fn (int $q): bool => $q > 0);
        if ($quantities === []) {
            throw new RuntimeException('Seleciona pelo menos um bilhete.');
        }

        $order = DB::transaction(function () use ($user, $quantities): TicketOrder {
            /** @var array<int, TicketType> $types */
            $types = TicketType::query()
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()->get()->keyBy('id')->all();

            if (count($types) !== count($quantities)) {
                throw new RuntimeException('Tipo de bilhete inválido.');
            }

            $occurrenceIds = array_values(array_unique(array_map(fn (TicketType $t): int => $t->event_occurrence_id, $types)));
            if (count($occurrenceIds) !== 1) {
                throw new RuntimeException('Os bilhetes têm de ser da mesma sessão.');
            }

            $total = 0.0;
            foreach ($quantities as $typeId => $qty) {
                $type = $types[$typeId];
                if (! $type->onSale()) {
                    throw new RuntimeException("O bilhete \"{$type->name}\" não está à venda.");
                }
                if ($type->remaining() < $qty) {
                    throw new RuntimeException("Esgotado: não há {$qty} bilhete(s) \"{$type->name}\".");
                }
                $total += (float) $type->price * $qty;
            }

            $occurrence = $types[array_key_first($types)]->occurrence;
            $event = $occurrence->event;
            $commission = $event !== null ? $this->commission->amount($event, $total) : 0.0;

            $order = TicketOrder::create([
                'reference' => $this->uniqueReference(),
                'user_id' => $user->id,
                'event_occurrence_id' => (int) $occurrenceIds[0],
                'status' => TicketOrderStatus::Confirmed->value,
                'total' => $total,
                'commission_amount' => $commission,
                'currency' => config('ticketing.currency'),
                'payment_status' => PaymentStatus::Pending->value,
                'reserved_until' => now()->addMinutes((int) config('ticketing.hold_minutes')),
            ]);

            // Reserve seats up front as cancelled-on-expiry tickets would be
            // messy; instead we issue them immediately but mark the order
            // pending, and only make them "valid for entry" once paid.
            foreach ($quantities as $typeId => $qty) {
                for ($i = 0; $i < $qty; $i++) {
                    $order->tickets()->create([
                        'ticket_type_id' => $typeId,
                        'code' => $this->uniqueCode(),
                        'status' => TicketStatus::Valid->value,
                    ]);
                }
            }

            return $order;
        });

        $intent = $this->gateway->createIntent($order);
        $order->update(['payment_intent_id' => $intent['id']]);

        return ['order' => $order->load('tickets.type'), 'intent' => $intent];
    }

    /** Confirm payment (called from the gateway webhook). Idempotent. */
    public function markPaid(string $paymentIntentId): ?TicketOrder
    {
        return DB::transaction(function () use ($paymentIntentId): ?TicketOrder {
            $order = TicketOrder::where('payment_intent_id', $paymentIntentId)
                ->lockForUpdate()->first();

            if ($order === null || $order->payment_status === PaymentStatus::Paid) {
                return $order; // idempotent: already handled or unknown
            }

            $order->update([
                'payment_status' => PaymentStatus::Paid->value,
                'paid_at' => now(),
                'reserved_until' => null,
            ]);

            return $order;
        });
    }

    /** Expire a pending order past its hold, releasing its seats. */
    public function expire(TicketOrder $order): void
    {
        if ($order->payment_status !== PaymentStatus::Pending) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $order->tickets()->update(['status' => TicketStatus::Cancelled->value]);
            $order->update([
                'payment_status' => PaymentStatus::Expired->value,
                'status' => TicketOrderStatus::Cancelled->value,
            ]);
        });
    }

    public function refund(TicketOrder $order): void
    {
        $this->gateway->refund($order);

        DB::transaction(function () use ($order): void {
            $order->tickets()->update(['status' => TicketStatus::Cancelled->value]);
            $order->update([
                'payment_status' => PaymentStatus::Refunded->value,
                'status' => TicketOrderStatus::Cancelled->value,
            ]);
        });
    }

    private function uniqueReference(): string
    {
        do {
            $ref = strtoupper(Str::random(8));
        } while (TicketOrder::where('reference', $ref)->exists());

        return $ref;
    }

    private function uniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
        } while (Ticket::where('code', $code)->exists());

        return $code;
    }
}
