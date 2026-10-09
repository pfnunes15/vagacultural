<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Promoter;
use App\Models\User;
use App\Services\Recommendations\RecommendationService;

function recEvent(array $attrs = [], ?Promoter $promoter = null): Event
{
    $event = Event::factory()->published()->create([
        'promoter_id' => ($promoter ?? Promoter::factory()->create())->id,
        ...$attrs,
    ]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDays(rand(1, 20))]);

    return $event;
}

function favoriteCategory(User $user, Category $category): void
{
    Favorite::create(['user_id' => $user->id, 'favoritable_type' => $category->getMorphClass(), 'favoritable_id' => $category->id]);
}

it('ranks events in a favorite category above unrelated ones', function (): void {
    $user = User::factory()->create();
    $music = Category::factory()->create();
    favoriteCategory($user, $music);

    $wanted = recEvent(['title' => ['pt' => 'Do meu gosto']]);
    $wanted->categories()->attach($music, ['is_primary' => true]);
    recEvent(['title' => ['pt' => 'Nao relacionado']]);

    $recs = app(RecommendationService::class)->for($user);

    expect($recs->first()->id)->toBe($wanted->id)
        ->and($recs->first()->recommendation_reason)->toContain('gostas de');
});

it('boosts events from a followed promoter', function (): void {
    $user = User::factory()->create();
    $promoter = Promoter::factory()->create(['name' => 'Seguido']);
    Follow::create(['user_id' => $user->id, 'followable_type' => $promoter->getMorphClass(), 'followable_id' => $promoter->id]);

    $followed = recEvent(['title' => ['pt' => 'Do seguido']], $promoter);
    recEvent(['title' => ['pt' => 'Doutro']]);

    $recs = app(RecommendationService::class)->for($user);

    expect($recs->first()->id)->toBe($followed->id)
        ->and($recs->first()->recommendation_reason)->toContain('segues');
});

it('excludes events already in the agenda', function (): void {
    $user = User::factory()->create();
    $inAgenda = recEvent();
    $user->agendaItems()->create(['event_id' => $inAgenda->id]);
    recEvent();

    $recs = app(RecommendationService::class)->for($user);

    expect($recs->pluck('id'))->not->toContain($inAgenda->id);
});

it('falls back to featured events for a user with no signals (cold start)', function (): void {
    $user = User::factory()->create();
    recEvent(['title' => ['pt' => 'Normal']]);
    $featured = recEvent(['title' => ['pt' => 'Destaque'], 'is_featured' => true]);

    $recs = app(RecommendationService::class)->for($user);

    expect($recs)->not->toBeEmpty()
        ->and($recs->first()->id)->toBe($featured->id);
});

it('serves recommendations on the web and requires login', function (): void {
    $this->get(route('my.recommendations'))->assertRedirect(route('login'));

    recEvent(['title' => ['pt' => 'Visivel Rec']]);
    $this->actingAs(User::factory()->create())
        ->get(route('my.recommendations', ['lang' => 'pt']))->assertOk()->assertSee('Visivel Rec');
});

it('serves recommendations via the API with a reason', function (): void {
    $user = User::factory()->create();
    $music = Category::factory()->create();
    favoriteCategory($user, $music);
    $e = recEvent();
    $e->categories()->attach($music, ['is_primary' => true]);

    $token = $user->createToken('app')->plainTextToken;
    $this->withToken($token)->getJson('/api/v1/me/recommendations')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'recommendation_reason']]]);
});

it('requires a token for the recommendations API', function (): void {
    $this->getJson('/api/v1/me/recommendations')->assertUnauthorized();
});
