<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Organization;
use App\Models\Promoter;

function publishedEventFor(Promoter $promoter, string $title): Event
{
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id, 'title' => $title]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(2)]);

    return $event;
}

it('shows a promoter public profile with their upcoming events', function (): void {
    $promoter = Promoter::factory()->create(['name' => 'Teatro Vivo']);
    publishedEventFor($promoter, 'Peça da Casa');
    $other = publishedEventFor(Promoter::factory()->create(), 'Evento de Outro');

    $this->get(route('promoters.show', $promoter))
        ->assertOk()
        ->assertSee('Teatro Vivo')
        ->assertSee('Peça da Casa')
        ->assertDontSee('Evento de Outro');
});

it('404s an inactive promoter profile', function (): void {
    $promoter = Promoter::factory()->create(['is_active' => false]);
    $this->get(route('promoters.show', $promoter))->assertNotFound();
});

it('shows an organization profile with events from its promoters', function (): void {
    $org = Organization::factory()->create(['name' => 'Casa da Cultura']);
    $promoter = Promoter::factory()->create(['organization_id' => $org->id]);
    publishedEventFor($promoter, 'Concerto da Organização');

    $this->get(route('organizations.show', $org))
        ->assertOk()
        ->assertSee('Casa da Cultura')
        ->assertSee('Concerto da Organização');
});

it('lists promoters and organizations publicly', function (): void {
    Promoter::factory()->create(['name' => 'Promotor Listado']);
    Organization::factory()->create(['name' => 'Org Listada']);

    $this->get(route('promoters.index'))->assertOk()->assertSee('Promotor Listado');
    $this->get(route('organizations.index'))->assertOk()->assertSee('Org Listada');
});

it('filters events by category page', function (): void {
    $cat = Category::factory()->create(['slug' => 'teatro-x']);
    $promoter = Promoter::factory()->create();
    $inCat = publishedEventFor($promoter, 'Tem Categoria');
    $inCat->categories()->attach($cat, ['is_primary' => true]);
    publishedEventFor($promoter, 'Fora da Categoria');

    $this->get(route('events.category', $cat))
        ->assertOk()
        ->assertSee('Tem Categoria')
        ->assertDontSee('Fora da Categoria');
});

it('searches events by title and promoter name', function (): void {
    $promoter = Promoter::factory()->create(['name' => 'Orquestra ABC']);
    publishedEventFor($promoter, 'Noite de Fados');
    publishedEventFor(Promoter::factory()->create(['name' => 'Outro']), 'Workshop de Cerâmica');

    // by title
    $this->get(route('events.index', ['q' => 'Fados']))
        ->assertOk()->assertSee('Noite de Fados')->assertDontSee('Workshop de Cerâmica');

    // by promoter name
    $this->get(route('events.index', ['q' => 'Orquestra ABC']))
        ->assertOk()->assertSee('Noite de Fados');
});
