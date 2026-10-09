<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\User;

function publishedEventWithOccurrence(array $attrs = []): Event
{
    $event = Event::factory()->published()->create($attrs);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(2)]);

    return $event;
}

it('lists upcoming published events publicly (no token)', function (): void {
    publishedEventWithOccurrence(['title' => 'Público Visível']);
    Event::factory()->create(['title' => 'Rascunho Escondido']); // draft, no occurrence

    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'tickets', 'min_age']], 'meta', 'links'])
        ->assertJsonFragment(['title' => 'Público Visível'])
        ->assertJsonMissing(['title' => 'Rascunho Escondido']);
});

it('filters the list by category', function (): void {
    $cat = Category::factory()->create(['slug' => 'concertos-x']);
    $concert = publishedEventWithOccurrence(['title' => 'Com Categoria']);
    $concert->categories()->attach($cat, ['is_primary' => true]);
    publishedEventWithOccurrence(['title' => 'Sem Categoria']);

    $this->getJson('/api/v1/events?categoria=concertos-x')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Com Categoria'])
        ->assertJsonMissing(['title' => 'Sem Categoria']);
});

it('returns the calendar grouped by day publicly', function (): void {
    publishedEventWithOccurrence(['title' => 'No Calendário']);

    $this->getJson('/api/v1/events/calendar')
        ->assertOk()
        ->assertJsonStructure(['data' => [['date', 'occurrences']]]);
});

it('requires a token for the event detail (the gate)', function (): void {
    $event = publishedEventWithOccurrence();

    $this->getJson("/api/v1/events/{$event->slug}")->assertUnauthorized();
});

it('returns full event detail with a token', function (): void {
    $cat = Category::factory()->create();
    $event = publishedEventWithOccurrence(['title' => 'Detalhe Completo', 'min_age' => 12, 'is_free' => false]);
    $event->categories()->attach($cat, ['is_primary' => true]);
    $event->tags()->create(['name' => 'jazz', 'slug' => 'jazz-x']);
    $event->ticketTiers()->create(['name' => 'Adulto', 'price' => 10, 'min_age' => 18, 'position' => 0]);

    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->getJson("/api/v1/events/{$event->slug}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Detalhe Completo')
        ->assertJsonPath('data.min_age.value', 12)
        ->assertJsonPath('data.min_age.label', '12+')
        ->assertJsonPath('data.tickets.is_free', false)
        ->assertJsonCount(1, 'data.categories')
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonCount(1, 'data.tickets.tiers')
        ->assertJsonCount(1, 'data.occurrences');
});

it('404s on a non-published event detail even with a token', function (): void {
    $event = Event::factory()->create(['status' => EventStatus::Pending]);
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->getJson("/api/v1/events/{$event->slug}")->assertNotFound();
});
