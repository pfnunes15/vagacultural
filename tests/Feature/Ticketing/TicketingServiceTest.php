<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\TicketType;
use App\Models\User;
use App\Services\Ticketing\TicketingService;

function occurrence(): EventOccurrence
{
    $event = Event::factory()->published()->create();

    return EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addWeek()]);
}

it('registers a free order and issues tickets with unique codes', function (): void {
    $occ = occurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => 50]);
    $user = User::factory()->create();

    $order = app(TicketingService::class)->register($user, [$type->id => 3]);

    expect($order->tickets)->toHaveCount(3)
        ->and((float) $order->total)->toBe(0.0)
        ->and($order->tickets->pluck('code')->unique())->toHaveCount(3)
        ->and($order->tickets->first()->status)->toBe(TicketStatus::Valid);
    expect($type->fresh()->remaining())->toBe(47);
});

it('refuses to oversell capacity', function (): void {
    $occ = occurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Limitado', 'price' => 0, 'capacity' => 2]);
    $user = User::factory()->create();

    app(TicketingService::class)->register($user, [$type->id => 2]);

    expect(fn () => app(TicketingService::class)->register(User::factory()->create(), [$type->id => 1]))
        ->toThrow(RuntimeException::class, 'Esgotado');
});

it('does not oversell under concurrent-style sequential contention at the boundary', function (): void {
    $occ = occurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Único', 'price' => 0, 'capacity' => 1]);

    app(TicketingService::class)->register(User::factory()->create(), [$type->id => 1]);

    expect($type->fresh()->remaining())->toBe(0)
        ->and(fn () => app(TicketingService::class)->register(User::factory()->create(), [$type->id => 1]))
        ->toThrow(RuntimeException::class);
});

it('rejects ticket types from different occurrences in one order', function (): void {
    $a = TicketType::create(['event_occurrence_id' => occurrence()->id, 'name' => 'A', 'price' => 0, 'capacity' => 10]);
    $b = TicketType::create(['event_occurrence_id' => occurrence()->id, 'name' => 'B', 'price' => 0, 'capacity' => 10]);

    expect(fn () => app(TicketingService::class)->register(User::factory()->create(), [$a->id => 1, $b->id => 1]))
        ->toThrow(RuntimeException::class, 'mesma sessão');
});

it('respects the sales window', function (): void {
    $occ = occurrence();
    $closed = TicketType::create([
        'event_occurrence_id' => $occ->id, 'name' => 'Fechado', 'price' => 0, 'capacity' => 10,
        'sales_end_at' => now()->subDay(),
    ]);

    expect(fn () => app(TicketingService::class)->register(User::factory()->create(), [$closed->id => 1]))
        ->toThrow(RuntimeException::class, 'não está à venda');
});

it('checks a ticket in once and rejects reuse', function (): void {
    $occ = occurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => 10]);
    $order = app(TicketingService::class)->register(User::factory()->create(), [$type->id => 1]);
    $ticket = $order->tickets->first();
    $staff = User::factory()->create();

    $first = app(TicketingService::class)->checkIn($ticket->code, $staff);
    expect($first['status'])->toBe('ok')
        ->and($ticket->fresh()->status)->toBe(TicketStatus::Used);

    $second = app(TicketingService::class)->checkIn($ticket->code, $staff);
    expect($second['status'])->toBe('already_used');
});

it('releases seats when an order is cancelled', function (): void {
    $occ = occurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => 5]);
    $order = app(TicketingService::class)->register(User::factory()->create(), [$type->id => 3]);

    expect($type->fresh()->remaining())->toBe(2);

    app(TicketingService::class)->cancel($order);

    expect($type->fresh()->remaining())->toBe(5);
});
