<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;
use App\Services\Ticketing\CommissionResolver;
use App\Services\Ticketing\PaymentService;

function paidOcc(float $price = 20.0, ?float $promoterRate = null, ?float $eventRate = null): array
{
    $promoter = Promoter::factory()->create(['commission_rate' => $promoterRate]);
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id, 'commission_rate' => $eventRate]);
    $occ = EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addWeek()]);
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Plateia', 'price' => $price, 'capacity' => 100]);

    return [$event, $occ, $type];
}

it('resolves commission in cascade: event over promoter over default', function (): void {
    config(['ticketing.default_commission_rate' => 8.0]);
    $resolver = app(CommissionResolver::class);

    [$eventA] = paidOcc(20, null, null);
    expect($resolver->rateForEvent($eventA))->toBe(8.0); // default

    [$eventB] = paidOcc(20, 12.0, null);
    expect($resolver->rateForEvent($eventB))->toBe(12.0); // promoter

    [$eventC] = paidOcc(20, 12.0, 5.0);
    expect($resolver->rateForEvent($eventC))->toBe(5.0); // event wins
});

it('starts a paid checkout: holds inventory, computes commission, creates an intent', function (): void {
    config(['ticketing.default_commission_rate' => 10.0]);
    [, $occ, $type] = paidOcc(20.0);
    $user = User::factory()->create();

    $result = app(PaymentService::class)->startCheckout($user, [$type->id => 2]);
    $order = $result['order'];

    expect((float) $order->total)->toBe(40.0)
        ->and((float) $order->commission_amount)->toBe(4.0) // 10% of 40
        ->and($order->payment_status)->toBe(PaymentStatus::Pending)
        ->and($order->reserved_until)->not->toBeNull()
        ->and($order->tickets)->toHaveCount(2)
        ->and($result['intent']['id'])->toStartWith('pi_fake_')
        ->and($order->payment_intent_id)->toBe($result['intent']['id']);

    // seats are held
    expect($type->fresh()->remaining())->toBe(98);
});

it('marks an order paid from the webhook and is idempotent', function (): void {
    [, , $type] = paidOcc(15.0);
    $result = app(PaymentService::class)->startCheckout(User::factory()->create(), [$type->id => 1]);
    $pi = $result['intent']['id'];

    $first = app(PaymentService::class)->markPaid($pi);
    expect($first->payment_status)->toBe(PaymentStatus::Paid)
        ->and($first->paid_at)->not->toBeNull()
        ->and($first->reserved_until)->toBeNull();

    // calling again does not change state (idempotent)
    $second = app(PaymentService::class)->markPaid($pi);
    expect($second->payment_status)->toBe(PaymentStatus::Paid);
});

it('expires a pending order and releases its seats', function (): void {
    [, , $type] = paidOcc(15.0);
    $result = app(PaymentService::class)->startCheckout(User::factory()->create(), [$type->id => 3]);
    $order = $result['order'];
    expect($type->fresh()->remaining())->toBe(97);

    app(PaymentService::class)->expire($order);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Expired)
        ->and($type->fresh()->remaining())->toBe(100)
        ->and($order->tickets->first()->fresh()->status)->toBe(TicketStatus::Cancelled);
});

it('does not expire an already-paid order', function (): void {
    [, , $type] = paidOcc(15.0);
    $result = app(PaymentService::class)->startCheckout(User::factory()->create(), [$type->id => 1]);
    app(PaymentService::class)->markPaid($result['intent']['id']);

    app(PaymentService::class)->expire($result['order']->fresh());

    expect($result['order']->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('refunds a paid order and releases seats', function (): void {
    [, , $type] = paidOcc(15.0);
    $result = app(PaymentService::class)->startCheckout(User::factory()->create(), [$type->id => 2]);
    app(PaymentService::class)->markPaid($result['intent']['id']);

    app(PaymentService::class)->refund($result['order']->fresh());

    expect($result['order']->fresh()->payment_status)->toBe(PaymentStatus::Refunded)
        ->and($type->fresh()->remaining())->toBe(100);
});
