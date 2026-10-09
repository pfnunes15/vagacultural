<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\User;

function searchableEvent(array $attrs = [], ?Promoter $promoter = null): Event
{
    $event = Event::factory()->published()->create([
        'promoter_id' => ($promoter ?? Promoter::factory()->create())->id,
        ...$attrs,
    ]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(2)]);

    return $event;
}

it('finds events by title through the search endpoint', function (): void {
    searchableEvent(['title' => ['pt' => 'Concerto de Música Rock']]);
    searchableEvent(['title' => ['pt' => 'Exposição de Pintura']]);

    $this->get(route('events.index', ['q' => 'rock', 'lang' => 'pt']))
        ->assertOk()
        ->assertSee('Concerto de Música Rock')
        ->assertDontSee('Exposição de Pintura');
});

it('finds events in any indexed locale', function (): void {
    searchableEvent(['title' => ['pt' => 'Sessão da Meia-Noite', 'en' => 'Midnight Session']]);

    // query a token that exists only in the English translation
    $this->get(route('events.index', ['q' => 'Midnight', 'lang' => 'pt']))
        ->assertOk()->assertSee('Sessão da Meia-Noite');
});

it('finds events by promoter name', function (): void {
    $promoter = Promoter::factory()->create(['name' => 'Orquestra Clássica']);
    searchableEvent(['title' => ['pt' => 'Serata']], $promoter);

    $this->get(route('events.index', ['q' => 'Orquestra Clássica', 'lang' => 'pt']))
        ->assertOk()->assertSee('Serata');
});

it('logs a search signal', function (): void {
    $this->get(route('events.index', ['q' => 'teatro']))->assertOk();

    $this->assertDatabaseHas('user_activity_log', ['action' => 'search']);
});

it('logs an event view signal', function (): void {
    $event = searchableEvent(['title' => ['pt' => 'Visível']]);

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))->assertOk();

    $this->assertDatabaseHas('user_activity_log', [
        'action' => 'event.view',
        'subject_type' => $event->getMorphClass(),
        'subject_id' => $event->id,
    ]);
});
