<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Models\Event;
use App\Models\EventOccurrence;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Read-side queries for the public (visitor-facing) events area.
 * Only publicly visible events are ever returned.
 */
class PublicEventService
{
    /**
     * Published events that still have an upcoming occurrence, paginated,
     * ordered by their next occurrence.
     *
     * @return LengthAwarePaginator<int, Event>
     */
    public function upcomingList(?string $categorySlug = null, int $perPage = 12): LengthAwarePaginator
    {
        return Event::query()
            ->published()
            ->whereHas('occurrences', fn ($q) => $q->where('starts_at', '>=', now()))
            ->when($categorySlug, fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->where('slug', $categorySlug),
            ))
            ->with([
                'categories',
                'promoter',
                'occurrences' => fn ($q) => $q->upcoming()->with('venue'),
            ])
            ->withMin('occurrences as next_occurrence_at', 'starts_at')
            ->orderBy('next_occurrence_at')
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
