<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\User;

it('shows the public events list to guests', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Concerto de Outono']);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);

    $this->get('/eventos')
        ->assertOk()
        ->assertSee('Concerto de Outono');
});

it('shows the public calendar to guests', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Exposição Atlântica']);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(3)]);

    $this->get('/calendario')
        ->assertOk()
        ->assertSee('Exposição Atlântica');
});

it('redirects guests from the event detail to login', function (): void {
    $event = Event::factory()->published()->create();

    $this->get(route('events.show', $event))
        ->assertRedirect(route('login'));
});

it('lets a logged-in user see the event detail', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Peça: A Tempestade']);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('A Tempestade');
});

it('hides non-published events from the detail page even when logged in', function (): void {
    $event = Event::factory()->create(); // draft

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertNotFound();
});

it('sends the guest back to the event after logging in', function (): void {
    $event = Event::factory()->published()->create();
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);
    $user = User::factory()->create(['email' => 'ana@example.pt', 'password' => 'password1234']);

    // hitting the gated page stores the intended url
    $this->get(route('events.show', $event))->assertRedirect(route('login'));

    $this->post('/entrar', ['email' => 'ana@example.pt', 'password' => 'password1234'])
        ->assertRedirect(route('events.show', $event));
});
