<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\User;

function published(): Event
{
    $event = Event::factory()->published()->create();
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(2)]);

    return $event;
}

it('toggles a favorite on the web and lists it', function (): void {
    $user = User::factory()->create();
    $event = published();

    $this->actingAs($user)->post(route('my.favorites.toggle', $event))->assertRedirect();
    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'favoritable_type' => $event->getMorphClass(),
        'favoritable_id' => $event->id,
    ]);

    $this->actingAs($user)->get(route('my.favorites'))->assertOk()->assertSee($event->title);

    // toggle off
    $this->actingAs($user)->post(route('my.favorites.toggle', $event))->assertRedirect();
    $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'favoritable_id' => $event->id]);
});

it('toggles an agenda item on the web', function (): void {
    $user = User::factory()->create();
    $event = published();

    $this->actingAs($user)->post(route('my.agenda.toggle', $event))->assertRedirect();
    $this->assertDatabaseHas('agenda_items', ['user_id' => $user->id, 'event_id' => $event->id]);

    $this->actingAs($user)->get(route('my.agenda'))->assertOk()->assertSee($event->title);

    $this->actingAs($user)->post(route('my.agenda.toggle', $event))->assertRedirect();
    $this->assertDatabaseMissing('agenda_items', ['user_id' => $user->id, 'event_id' => $event->id]);
});

it('requires login for favorites and agenda (web)', function (): void {
    $event = published();
    $this->get(route('my.favorites'))->assertRedirect(route('login'));
    $this->post(route('my.favorites.toggle', $event))->assertRedirect(route('login'));
    $this->get(route('my.agenda'))->assertRedirect(route('login'));
});

it('toggles favorite and lists favorites via the API', function (): void {
    $event = published();
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->postJson("/api/v1/events/{$event->slug}/favorite")
        ->assertOk()->assertJson(['favorited' => true]);

    $this->withToken($token)->getJson('/api/v1/me/favorites')
        ->assertOk()->assertJsonFragment(['slug' => $event->slug]);

    $this->withToken($token)->postJson("/api/v1/events/{$event->slug}/favorite")
        ->assertOk()->assertJson(['favorited' => false]);
});

it('toggles agenda via the API', function (): void {
    $event = published();
    $token = User::factory()->create()->createToken('app')->plainTextToken;

    $this->withToken($token)->postJson("/api/v1/events/{$event->slug}/agenda")
        ->assertOk()->assertJson(['in_agenda' => true]);

    $this->withToken($token)->getJson('/api/v1/me/agenda')
        ->assertOk()->assertJsonFragment(['slug' => $event->slug]);
});

it('requires a token for engagement endpoints (API)', function (): void {
    $event = published();
    $this->postJson("/api/v1/events/{$event->slug}/favorite")->assertUnauthorized();
    $this->getJson('/api/v1/me/favorites')->assertUnauthorized();
    $this->getJson('/api/v1/me/agenda')->assertUnauthorized();
});
