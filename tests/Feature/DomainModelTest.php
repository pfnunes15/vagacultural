<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\AgendaItem;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Favorite;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Venue;

it('builds an event with occurrences, categories, venue and promoter', function (): void {
    $promoter = Promoter::factory()->create();
    $venue = Venue::factory()->create();
    $category = Category::factory()->create();

    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id]);
    $event->categories()->attach($category, ['is_primary' => true]);
    EventOccurrence::factory()->count(3)->create([
        'event_id' => $event->id,
        'venue_id' => $venue->id,
    ]);

    expect($event->promoter->is($promoter))->toBeTrue()
        ->and($event->categories)->toHaveCount(1)
        ->and((bool) $event->categories->first()->pivot->is_primary)->toBeTrue()
        ->and($event->occurrences)->toHaveCount(3)
        ->and($event->occurrences->first()->venue->is($venue))->toBeTrue()
        ->and($event->status)->toBe(EventStatus::Published);
});

it('scopes published events only', function (): void {
    Event::factory()->create(); // draft
    Event::factory()->published()->create();

    expect(Event::query()->published()->count())->toBe(1);
});

it('assigns roles and detects admin', function (): void {
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::Admin->value]);

    expect($user->isAdmin())->toBeTrue()
        ->and($user->roles->first()->role)->toBe(UserRole::Admin);
});

it('lets a user favorite an event polymorphically and add it to the agenda', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create();

    Favorite::create([
        'user_id' => $user->id,
        'favoritable_type' => Event::class,
        'favoritable_id' => $event->id,
    ]);
    AgendaItem::create(['user_id' => $user->id, 'event_id' => $event->id]);

    expect($user->favorites()->count())->toBe(1)
        ->and($user->favorites->first()->favoritable->is($event))->toBeTrue()
        ->and($user->agendaItems()->count())->toBe(1);
});

it('finds upcoming occurrences ordered by start', function (): void {
    $event = Event::factory()->create();
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->subDay()]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(2)]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);

    $upcoming = EventOccurrence::query()->upcoming()->get();

    expect($upcoming)->toHaveCount(2)
        ->and($upcoming->first()->starts_at->lt($upcoming->last()->starts_at))->toBeTrue();
});
