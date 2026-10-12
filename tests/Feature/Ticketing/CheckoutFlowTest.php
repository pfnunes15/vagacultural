<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;

function paidOccHttp(float $price = 20.0): array
{
    $promoter = Promoter::factory()->create();
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id]);
    $occ = EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addWeek()]);
    $paid = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Plateia', 'price' => $price, 'capacity' => 50]);
    $free = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Livre', 'price' => 0, 'capacity' => 50]);

    return [$occ, $paid, $free];
}

it('routes a free registration straight to confirmed tickets', function (): void {
    [$occ, , $free] = paidOccHttp();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('my.occurrences.register', $occ), ['quantities' => [$free->id => 1]])
        ->assertRedirect(route('my.tickets'));

    expect($user->ticketOrders()->first()->payment_status)->toBe(PaymentStatus::Free);
});

it('routes a paid registration to the checkout page as pending', function (): void {
    [$occ, $paid] = paidOccHttp();
    $user = User::factory()->create();

    $resp = $this->actingAs($user)->post(route('my.occurrences.register', $occ), ['quantities' => [$paid->id => 2]]);

    $order = $user->ticketOrders()->first();
    $resp->assertRedirect(route('my.checkout', $order));
    expect($order->payment_status)->toBe(PaymentStatus::Pending)
        ->and($order->reserved_until)->not->toBeNull();
});

it('confirms a simulated payment and shows the tickets', function (): void {
    [$occ, $paid] = paidOccHttp();
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('my.occurrences.register', $occ), ['quantities' => [$paid->id => 1]]);
    $order = $user->ticketOrders()->first();

    $this->actingAs($user)->get(route('my.checkout', $order))->assertOk()->assertSee('Pagar');
    $this->actingAs($user)->post(route('my.checkout.pay', $order))->assertRedirect(route('my.tickets'));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
    $this->actingAs($user)->get(route('my.tickets'))->assertOk()->assertSee($order->reference);
});

it('forbids paying for someone else\'s order', function (): void {
    [$occ, $paid] = paidOccHttp();
    $buyer = User::factory()->create();
    $this->actingAs($buyer)->post(route('my.occurrences.register', $occ), ['quantities' => [$paid->id => 1]]);
    $order = $buyer->ticketOrders()->first();

    $this->actingAs(User::factory()->create())->get(route('my.checkout', $order))->assertForbidden();
    $this->actingAs(User::factory()->create())->post(route('my.checkout.pay', $order))->assertForbidden();
});

it('cancelling checkout releases the held seats', function (): void {
    [$occ, $paid] = paidOccHttp();
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('my.occurrences.register', $occ), ['quantities' => [$paid->id => 2]]);
    $order = $user->ticketOrders()->first();
    expect($paid->fresh()->remaining())->toBe(48);

    $this->actingAs($user)->post(route('my.checkout.cancel', $order))->assertRedirect();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Expired)
        ->and($paid->fresh()->remaining())->toBe(50);
});

it('expires unpaid holds via the scheduled command', function (): void {
    [$occ, $paid] = paidOccHttp();
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('my.occurrences.register', $occ), ['quantities' => [$paid->id => 1]]);
    $order = $user->ticketOrders()->first();
    $order->update(['reserved_until' => now()->subMinute()]);

    $this->artisan('tickets:expire-holds')->assertSuccessful();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Expired)
        ->and($paid->fresh()->remaining())->toBe(50);
});

it('returns a payment intent from the API for a paid registration', function (): void {
    [$occ, $paid] = paidOccHttp();
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->postJson("/api/v1/occurrences/{$occ->id}/register", ['quantities' => [$paid->id => 1]])
        ->assertCreated()
        ->assertJsonStructure(['order' => ['reference'], 'payment' => ['client_secret', 'intent_id']]);
});
