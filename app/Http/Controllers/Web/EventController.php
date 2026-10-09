<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Events\PublicEventService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(private readonly PublicEventService $events) {}

    /** Public: browsable list of upcoming events. */
    public function index(Request $request): View
    {
        $category = $request->string('categoria')->value() ?: null;

        return view('events.index', [
            'events' => $this->events->upcomingList($category),
            'category' => $category,
        ]);
    }

    /** Public: calendar grouped by day. */
    public function calendar(): View
    {
        return view('events.calendar', [
            'days' => $this->events->calendar(),
        ]);
    }

    /**
     * Gated: event detail. Requires an authenticated (registered) user.
     * Only publicly visible events are shown; anything else is a 404.
     */
    public function show(Event $event): View
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $event->load([
            'categories',
            'promoter.organization',
            'images',
            'occurrences' => fn ($q) => $q->orderBy('starts_at')->with('venue'),
        ]);

        return view('events.show', ['event' => $event]);
    }
}
