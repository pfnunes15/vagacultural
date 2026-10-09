<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Favorite;
use App\Models\Tag;
use App\Models\User;
use App\Services\Recommendations\RecommendationService;

function taggedPublished(Tag $tag, string $title): Event
{
    $e = Event::factory()->published()->create(['title' => ['pt' => $title]]);
    EventOccurrence::factory()->create(['event_id' => $e->id, 'starts_at' => now()->addDays(2)]);
    $e->tags()->attach($tag);

    return $e;
}

it('shows a public tag page listing its events (SEO)', function (): void {
    $tag = Tag::factory()->create(['name' => 'ao ar livre', 'slug' => 'ao-ar-livre']);
    taggedPublished($tag, 'Festival ao Ar Livre');
    $other = Event::factory()->published()->create(['title' => ['pt' => 'Sem Tag']]);
    EventOccurrence::factory()->create(['event_id' => $other->id, 'starts_at' => now()->addDay()]);

    $this->get(route('events.tag', ['tag' => $tag, 'lang' => 'pt']))
        ->assertOk()
        ->assertSee('Festival ao Ar Livre')
        ->assertDontSee('Sem Tag');
});

it('lets a user pick favorite tags in the profile', function (): void {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($user)->put(route('my.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'tags' => [$tag->id],
    ])->assertRedirect();

    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'favoritable_type' => $tag->getMorphClass(),
        'favoritable_id' => $tag->id,
    ]);
});

it('suggests existing tags by popularity', function (): void {
    $popular = Tag::factory()->create(['name' => 'jazz']);
    Tag::factory()->create(['name' => 'jazzinho']);
    foreach (range(1, 2) as $i) {
        Event::factory()->create()->tags()->attach($popular);
    }

    $this->getJson(route('tags.suggest', ['q' => 'jazz']))
        ->assertOk()
        ->assertJsonPath('data.0', 'jazz');
});

it('boosts events with a favorite tag in recommendations', function (): void {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'eletrónica']);
    Favorite::create(['user_id' => $user->id, 'favoritable_type' => $tag->getMorphClass(), 'favoritable_id' => $tag->id]);

    $wanted = taggedPublished($tag, 'Noite Eletrónica');
    $other = Event::factory()->published()->create(['title' => ['pt' => 'Outro']]);
    EventOccurrence::factory()->create(['event_id' => $other->id, 'starts_at' => now()->addDay()]);

    $recs = app(RecommendationService::class)->for($user);

    expect($recs->first()->id)->toBe($wanted->id)
        ->and($recs->first()->recommendation_reason)->toContain('eletrónica');
});
