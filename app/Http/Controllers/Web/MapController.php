<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EventOccurrence;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        return view('events.map');
    }

    /** Upcoming published occurrences that have venue coordinates, as points. */
    public function points(): JsonResponse
    {
        $points = EventOccurrence::query()
            ->upcoming()
            ->whereHas('event', fn ($q) => $q->published())
            ->whereHas('venue', fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->with(['event.categories', 'venue'])
            ->get()
            ->map(fn (EventOccurrence $o): array => [
                'lat' => (float) $o->venue->latitude,
                'lng' => (float) $o->venue->longitude,
                'title' => $o->event->title,
                'slug' => $o->event->slug,
                'venue' => $o->venue->name,
                'when' => $o->starts_at->translatedFormat('D, d M · H:i'),
                'category' => $o->event->categories->first()?->name,
            ])
            ->values();

        return response()->json(['data' => $points]);
    }
}
