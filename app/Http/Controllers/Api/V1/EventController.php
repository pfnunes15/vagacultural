<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventOccurrenceResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\Events\PublicEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function __construct(private readonly PublicEventService $events) {}

    /** Public: paginated list of upcoming published events. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = $request->string('categoria')->value() ?: null;

        return EventResource::collection($this->events->upcomingList($category));
    }

    /** Public: upcoming occurrences grouped by day. */
    public function calendar(): JsonResponse
    {
        $data = $this->events->calendar()
            ->map(fn ($occurrences, string $date): array => [
                'date' => $date,
                'occurrences' => EventOccurrenceResource::collection($occurrences),
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    /** Gated (token required): full event detail. Only published events. */
    public function show(Event $event): EventResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $event->load([
            'categories',
            'tags',
            'ticketTiers',
            'promoter.organization',
            'occurrences' => fn ($q) => $q->orderBy('starts_at')->with('venue'),
        ]);

        return new EventResource($event);
    }
}
