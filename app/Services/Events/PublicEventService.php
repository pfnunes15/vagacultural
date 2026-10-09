<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Read-side queries for the public (visitor-facing) events area.
 * Only publicly visible events are ever returned.
 */
class PublicEventService
{
    /**
     * Base query: published events with an upcoming occurrence, eager-loaded
     * and ordered by their next occurrence.
     *
     * @return Builder<Event>
     */
    private function baseUpcoming(): Builder
    {
        return Event::query()
            ->published()
            ->whereHas('occurrences', fn ($q) => $q->where('starts_at', '>=', now()))
            ->with([
                'categories',
                'promoter',
                'occurrences' => fn ($q) => $q->upcoming()->with('venue'),
            ])
            ->withMin('occurrences as next_occurrence_at', 'starts_at')
            ->orderBy('next_occurrence_at');
    }

    /**
     * Public list, optionally filtered by category slug and/or a search term.
     *
     * @return LengthAwarePaginator<int, Event>
     */
    public function upcomingList(?string $categorySlug = null, ?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $term = $search !== null ? trim($search) : null;

        // Full-text, typo-tolerant search via Scout (Meilisearch) when a term is given.
        if ($term !== null && $term !== '') {
            return Event::search($term)
                ->query(fn ($q) => $q->published()
                    ->whereHas('occurrences', fn ($o) => $o->where('starts_at', '>=', now()))
                    ->with([
                        'categories',
                        'promoter',
                        'occurrences' => fn ($o) => $o->upcoming()->with('venue'),
                    ]))
                ->paginate($perPage)
                ->withQueryString();
        }

        return $this->baseUpcoming()
            ->when($categorySlug, fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->where('slug', $categorySlug),
            ))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Upcoming published events carrying a given tag.
     *
     * @return LengthAwarePaginator<int, Event>
     */
    public function forTag(Tag $tag, int $perPage = 12): LengthAwarePaginator
    {
        return $this->baseUpcoming()
            ->whereHas('tags', fn ($q) => $q->whereKey($tag->id))
            ->paginate($perPage);
    }

    /**
     * Upcoming published events of a single promoter.
     *
     * @return LengthAwarePaginator<int, Event>
     */
    public function forPromoter(Promoter $promoter, int $perPage = 12): LengthAwarePaginator
    {
        return $this->baseUpcoming()->where('promoter_id', $promoter->id)->paginate($perPage);
    }

    /**
     * Upcoming published events across all promoters of an organization.
     *
     * @return LengthAwarePaginator<int, Event>
     */
    public function forOrganization(Organization $organization, int $perPage = 12): LengthAwarePaginator
    {
        return $this->baseUpcoming()
            ->whereHas('promoter', fn ($q) => $q->where('organization_id', $organization->id))
            ->paginate($perPage);
    }

    /**
     * Upcoming occurrences of published events, grouped by calendar day.
     *
     * @return Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, EventOccurrence>>
     */
    public function calendar(int $days = 30): Collection
    {
        return EventOccurrence::query()
            ->upcoming()
            ->where('starts_at', '<=', now()->addDays($days))
            ->whereHas('event', fn ($q) => $q->published())
            ->with(['event.categories', 'venue'])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (EventOccurrence $o): string => $o->starts_at->toDateString());
    }
}
