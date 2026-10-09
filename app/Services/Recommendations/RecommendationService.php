<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Phase 1 — content-based recommendations.
 *
 * Scores upcoming published events against the user's explicit signals
 * (favorite categories, followed promoters/organizations) and their history
 * (categories of events they favorited or put in their agenda). Each result
 * carries a human "reason" so the UI can explain why it was suggested.
 */
class RecommendationService
{
    private const SCORE_FOLLOWED_PROMOTER = 6;

    private const SCORE_FOLLOWED_ORGANIZATION = 5;

    private const SCORE_FAVORITE_CATEGORY = 4;

    private const SCORE_FAVORITE_TAG = 3;

    private const SCORE_HISTORY_CATEGORY = 2;

    private const SCORE_FEATURED = 1;

    /**
     * @return Collection<int, Event> events with a transient `recommendation_reason`
     */
    public function for(User $user, int $limit = 12): Collection
    {
        $signals = [
            'favoriteCategoryIds' => $this->favoriteCategoryIds($user),
            'favoriteTagIds' => $this->favoriteTagIds($user),
            'followedPromoterIds' => $this->followedIds($user, (new Promoter)->getMorphClass()),
            'followedOrganizationIds' => $this->followedIds($user, (new Organization)->getMorphClass()),
            'historyCategoryIds' => $this->historyCategoryIds($user),
        ];
        $alreadyInAgenda = $user->agendaItems()->pluck('event_id')->all();

        $candidates = Event::query()
            ->published()
            ->whereHas('occurrences', fn ($q) => $q->where('starts_at', '>=', now()))
            ->whereNotIn('id', $alreadyInAgenda)
            ->with(['categories', 'tags', 'promoter', 'occurrences' => fn ($q) => $q->upcoming()->with('venue')])
            ->withMin('occurrences as next_occurrence_at', 'starts_at')
            ->orderBy('next_occurrence_at')
            ->limit(200)
            ->get();

        $scored = $candidates
            ->map(function (Event $event) use ($signals): Event {
                [$score, $reason] = $this->score($event, $signals);
                $event->recommendation_score = $score;
                $event->recommendation_reason = $reason;

                return $event;
            })
            ->sortByDesc(fn (Event $e): int => $e->recommendation_score)
            ->values();

        // Cold start: no useful signals -> featured & soonest events.
        if ($scored->isEmpty() || $scored->every(fn (Event $e): bool => $e->recommendation_score === 0)) {
            return $candidates
                ->sortByDesc(fn (Event $e): bool => $e->is_featured)
                ->take($limit)
                ->each(fn (Event $e) => $e->recommendation_reason = $e->is_featured ? 'Em destaque' : 'A acontecer em breve')
                ->values();
        }

        return $scored->take($limit);
    }

    /**
     * @param  array{favoriteCategoryIds: list<int>, favoriteTagIds: list<int>, followedPromoterIds: list<int>, followedOrganizationIds: list<int>, historyCategoryIds: list<int>}  $signals
     * @return array{int, string|null}
     */
    private function score(Event $event, array $signals): array
    {
        $score = 0;
        $reasons = [];
        $eventCategoryIds = $event->categories->pluck('id')->all();
        $eventTagIds = $event->tags->pluck('id')->all();

        if (in_array($event->promoter_id, $signals['followedPromoterIds'], true)) {
            $score += self::SCORE_FOLLOWED_PROMOTER;
            $reasons[self::SCORE_FOLLOWED_PROMOTER] = 'Porque segues ' . $event->promoter->name;
        }

        $organizationId = $event->promoter?->organization_id;
        if ($organizationId !== null && in_array($organizationId, $signals['followedOrganizationIds'], true)) {
            $score += self::SCORE_FOLLOWED_ORGANIZATION;
            $reasons[self::SCORE_FOLLOWED_ORGANIZATION] = 'De uma organização que segues';
        }

        $favMatches = array_intersect($eventCategoryIds, $signals['favoriteCategoryIds']);
        if ($favMatches !== []) {
            $score += self::SCORE_FAVORITE_CATEGORY * count($favMatches);
            $name = $event->categories->firstWhere('id', reset($favMatches))?->name;
            $reasons[self::SCORE_FAVORITE_CATEGORY] = $name ? 'Porque gostas de ' . $name : 'Da tua categoria favorita';
        }

        $tagMatches = array_intersect($eventTagIds, $signals['favoriteTagIds']);
        if ($tagMatches !== []) {
            $score += self::SCORE_FAVORITE_TAG * count($tagMatches);
            $tagName = $event->tags->firstWhere('id', reset($tagMatches))?->name;
            $reasons[self::SCORE_FAVORITE_TAG] = $tagName ? 'Porque te interessa #' . $tagName : 'De uma tag que segues';
        }

        $histMatches = array_intersect($eventCategoryIds, $signals['historyCategoryIds']);
        if ($histMatches !== []) {
            $score += self::SCORE_HISTORY_CATEGORY;
            $reasons[self::SCORE_HISTORY_CATEGORY] = 'Parecido com eventos que guardaste';
        }

        if ($event->is_featured) {
            $score += self::SCORE_FEATURED;
            $reasons[self::SCORE_FEATURED] = 'Em destaque';
        }

        // Strongest reason wins (highest weight key).
        $reason = $reasons === [] ? null : $reasons[max(array_keys($reasons))];

        return [$score, $reason];
    }

    /** @return list<int> */
    private function favoriteCategoryIds(User $user): array
    {
        return $user->favorites()
            ->where('favoritable_type', (new Category)->getMorphClass())
            ->pluck('favoritable_id')->map('intval')->all();
    }

    /** @return list<int> */
    private function favoriteTagIds(User $user): array
    {
        return $user->favorites()
            ->where('favoritable_type', (new Tag)->getMorphClass())
            ->pluck('favoritable_id')->map('intval')->all();
    }

    /** @return list<int> */
    private function followedIds(User $user, string $type): array
    {
        return $user->follows()
            ->where('followable_type', $type)
            ->pluck('followable_id')->map('intval')->all();
    }

    /**
     * Category ids from events the user favorited or has in their agenda.
     *
     * @return list<int>
     */
    private function historyCategoryIds(User $user): array
    {
        $favoriteEventIds = $user->favorites()
            ->where('favoritable_type', (new Event)->getMorphClass())
            ->pluck('favoritable_id');
        $agendaEventIds = $user->agendaItems()->pluck('event_id');
        $eventIds = $favoriteEventIds->merge($agendaEventIds)->unique();

        if ($eventIds->isEmpty()) {
            return [];
        }

        return Category::query()
            ->whereHas('events', fn ($q) => $q->whereIn('events.id', $eventIds))
            ->pluck('id')->map('intval')->all();
    }
}
