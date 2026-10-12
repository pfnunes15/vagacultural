<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Models\Event;
use App\Models\User;
use App\Services\Recommendations\RecommendationService;
use Illuminate\Database\Eloquent\Collection;

/**
 * Aggregates the mobile app's home screen into a single payload, so the app
 * makes one request instead of several.
 *
 * @phpstan-type Section array{key: string, title: string, events: Collection<int, Event>}
 */
class HomeFeedService
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    /**
     * @return list<Section>
     */
    public function for(User $user): array
    {
        $sections = [];

        $recommended = $this->recommendations->for($user, 10);
        if ($recommended->isNotEmpty()) {
            $sections[] = ['key' => 'for_you', 'title' => 'Para ti', 'events' => $recommended];
        }

        $featured = $this->withRelations(
            Event::query()->published()
                ->where('is_featured', true)
                ->whereHas('occurrences', fn ($q) => $q->where('starts_at', '>=', now()))
                ->withMin('occurrences as next_occurrence_at', 'starts_at')
                ->orderBy('next_occurrence_at')
                ->limit(10)->get()
        );
        if ($featured->isNotEmpty()) {
            $sections[] = ['key' => 'featured', 'title' => 'Em destaque', 'events' => $featured];
        }

        $soon = $this->withRelations(
            Event::query()->published()
                ->whereHas('occurrences', fn ($q) => $q->where('starts_at', '>=', now()))
                ->withMin('occurrences as next_occurrence_at', 'starts_at')
                ->orderBy('next_occurrence_at')
                ->limit(10)->get()
        );
        if ($soon->isNotEmpty()) {
            $sections[] = ['key' => 'upcoming', 'title' => 'A não perder', 'events' => $soon];
        }

        return $sections;
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return Collection<int, Event>
     */
    private function withRelations(Collection $events): Collection
    {
        $events->load(['categories', 'promoter', 'occurrences' => fn ($q) => $q->upcoming()->with('venue')]);

        return $events;
    }
}
