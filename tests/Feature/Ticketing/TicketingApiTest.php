<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\Ticketing\TicketingService;

function occWithType(int $capacity = 50): array
{
    $owner = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $owner->id, 'role' => UserRole::Promoter->value]);
    $promoter = Promoter::factory()->create(['user_id' => $owner->id]);
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id]);
    $occ = EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addWeek()]);
    $type = TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => 0, 'capacity' => $capacity]);

    return [$owner, $occ, $type];
}

it('lists ticket types for an occurrence (with token)', function (): void {
    [, $occ] = occWithType();
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->getJson("/api/v1/occurrences/{$occ->id}/ticket-types")
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Geral')
        ->assertJsonPath('data.0.remaining', 50);
});

it('registers via the API and lists my tickets', function (): void {
    [, $occ, $type] = occWithType();
    $user = User::factory()->create();
    $token = $user->createToken('app')->plainTextToken;

    $this->withToken($token)->postJson("/api/v1/occurrences/{$occ->id}/register", [
        'quantities' => [$type->id => 2],
    ])->assertCreated()->assertJsonCount(2, 'data.tickets');

    $this->withToken($token)->getJson('/api/v1/me/tickets')
        ->assertOk()->assertJsonPath('data.0.tickets.0.code', fn ($c) => is_string($c) && strlen($c) === 16);
});

it('returns 422 when registering over capacity via the API', function (): void {
    [, $occ, $type] = occWithType(1);
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->postJson("/api/v1/occurrences/{$occ->id}/register", [
        'quantities' => [$type->id => 3],
    ])->assertStatus(422);
});

it('lets staff check a ticket in via the API and blocks reuse', function (): void {
    [$owner, $occ, $type] = occWithType();
    $user = User::factory()->create();
    $order = app(TicketingService::class)->register($user, [$type->id => 1]);
    $code = $order->tickets->first()->code;
    $staffToken = $owner->createToken('app')->plainTextToken;

    $this->withToken($staffToken)->postJson("/api/v1/tickets/{$code}/check-in")
        ->assertOk()->assertJsonPath('status', 'ok');
    $this->withToken($staffToken)->postJson("/api/v1/tickets/{$code}/check-in")
        ->assertOk()->assertJsonPath('status', 'already_used');
});

it('forbids a non-staff user from checking tickets in', function (): void {
    [, $occ, $type] = occWithType();
    $user = User::factory()->create();
    $order = app(TicketingService::class)->register($user, [$type->id => 1]);
    $code = $order->tickets->first()->code;

    $this->withToken($user->createToken('app')->plainTextToken)
        ->postJson("/api/v1/tickets/{$code}/check-in")->assertForbidden();
});

it('requires a token for ticketing endpoints', function (): void {
    [, $occ] = occWithType();
    $this->getJson("/api/v1/occurrences/{$occ->id}/ticket-types")->assertUnauthorized();
    $this->getJson('/api/v1/me/tickets')->assertUnauthorized();
});

it('serves a QR image only to the ticket owner', function (): void {
    [, $occ, $type] = occWithType();
    $user = User::factory()->create();
    $order = app(TicketingService::class)->register($user, [$type->id => 1]);
    $ticket = $order->tickets->first();

    $this->actingAs($user)->get(route('my.tickets.qr', $ticket))
        ->assertOk()->assertHeader('Content-Type', 'image/svg+xml');

    $this->actingAs(User::factory()->create())->get(route('my.tickets.qr', $ticket))->assertForbidden();
});
