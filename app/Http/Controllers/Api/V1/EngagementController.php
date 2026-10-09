<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\Engagement\AgendaService;
use App\Services\Engagement\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EngagementController extends Controller
{
    public function __construct(
        private readonly FavoriteService $favorites,
        private readonly AgendaService $agenda,
    ) {}

    public function favorites(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->whereIn('id', $request->user()->favorites()
                ->where('favoritable_type', (new Event)->getMorphClass())
                ->pluck('favoritable_id'))
            ->published()
            ->with(['categories', 'occurrences' => fn ($q) => $q->upcoming()->with('venue')])
            ->paginate(20);

        return EventResource::collection($events);
    }

    public function toggleFavorite(Request $request, Event $event): JsonResponse
    {
        $favorited = $this->favorites->toggle($request->user(), $event);

        return response()->json(['favorited' => $favorited]);
    }

    public function agenda(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->whereIn('id', $request->user()->agendaItems()->pluck('event_id'))
            ->with(['categories', 'occurrences' => fn ($q) => $q->orderBy('starts_at')->with('venue')])
            ->paginate(20);

        return EventResource::collection($events);
    }

    public function toggleAgenda(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if ($this->agenda->has($user, $event)) {
            $this->agenda->remove($user, $event);

            return response()->json(['in_agenda' => false]);
        }

        $this->agenda->add($user, $event);

        return response()->json(['in_agenda' => true]);
    }
}
