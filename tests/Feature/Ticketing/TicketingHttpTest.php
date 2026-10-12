<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserRoleAssignment;

function promoterWithOccurrence(): array
{
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::Promoter->value]);
    $promoter = Promoter::factory()->create(['user_id' => $user->id]);
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id]);
    $occ = EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addWeek()]);

    return [$user, $occ];
}

it('lets the owner promoter create a ticket type', function (): void {
    [$owner, $occ] = promoterWithOccurrence();

    $this->actingAs($owner)->post(route('dashboard.occurrences.tickets.store', $occ), [
        'name' => 'Geral', 'price' => 0, 'capacity' => 100,
    ])->assertRedirect();

    $this->assertDatabaseHas('ticket_types', ['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'capacity' => 100]);
});

it('forbids a stranger from managing ticket types', function (): void {
    [, $occ] = promoterWithOccurrence();
    $stranger = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $stranger->id, 'role' => UserRole::Promoter->value]);
    Promoter::factory()->create(['user_id' => $stranger->id]);

    $this->actingAs($stranger)->get(route('dashboard.occurrences.tickets', $occ))->assertForbidden();
    $this->actingAs($stranger)->post(route('dashboard.occurrences.tickets.store', $occ), [
        'name' => 'X', 'price' => 0, 'capacity' => 10,
    ])->assertForbidden();
});

it('lets a logged-in user register for a free session', function (): void {
    [, $occ] = promoterWithOccurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => 50]);
    $attendee = User::factory()->create();

    $this->actingAs($attendee)->post(route('my.occurrences.register', $occ), [
        'quantities' => [$type->id => 2],
    ])->assertRedirect(route('my.tickets'));

    expect($attendee->ticketOrders()->count())->toBe(1)
        ->and($attendee->ticketOrders()->first()->tickets()->count())->toBe(2);

    $this->actingAs($attendee)->get(route('my.tickets'))->assertOk()->assertSee('Geral');
});

it('requires login to register', function (): void {
    [, $occ] = promoterWithOccurrence();
    $this->post(route('my.occurrences.register', $occ), ['quantities' => []])
        ->assertRedirect(route('login'));
});

it('shows an error when registering beyond capacity', function (): void {
    [, $occ] = promoterWithOccurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Mini', 'price' => 0, 'capacity' => 1]);

    $this->actingAs(User::factory()->create())->post(route('my.occurrences.register', $occ), [
        'quantities' => [$type->id => 5],
    ])->assertSessionHasErrors('tickets');
});

it('validates a ticket at the door and blocks reuse over HTTP', function (): void {
    [$owner, $occ] = promoterWithOccurrence();
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => 10]);
    $attendee = User::factory()->create();
    $this->actingAs($attendee)->post(route('my.occurrences.register', $occ), ['quantities' => [$type->id => 1]]);
    $code = $attendee->ticketOrders()->first()->tickets()->first()->code;

    $this->actingAs($owner)->post(route('dashboard.occurrences.checkin.scan', $occ), ['code' => $code])
        ->assertSessionHas('checkin', fn ($c): bool => $c['status'] === 'ok');

    $this->actingAs($owner)->post(route('dashboard.occurrences.checkin.scan', $occ), ['code' => $code])
        ->assertSessionHas('checkin', fn ($c): bool => $c['status'] === 'already_used');
});

it('rejects a ticket code from another occurrence at check-in', function (): void {
    [$owner, $occ] = promoterWithOccurrence();
    [, $otherOcc] = promoterWithOccurrence();
    $type = TicketType::create(['event_occurrence_id' => $otherOcc->id, 'name' => 'Outro', 'price' => 0, 'capacity' => 10]);
    $attendee = User::factory()->create();
    $this->actingAs($attendee)->post(route('my.occurrences.register', $otherOcc), ['quantities' => [$type->id => 1]]);
    $code = $attendee->ticketOrders()->first()->tickets()->first()->code;

    $this->actingAs($owner)->post(route('dashboard.occurrences.checkin.scan', $occ), ['code' => $code])
        ->assertSessionHas('checkin', fn ($c): bool => $c['status'] === 'wrong_event');
});
