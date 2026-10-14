<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Tag;
use App\Services\Analytics\ActivityLogger;
use App\Services\Events\PublicEventService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly PublicEventService $events,
        private readonly ActivityLogger $activity,
    ) {}

    /** Public: browsable list of upcoming events. */
    public function index(Request $request): View
    {
        $category = $request->string('category')->value() ?: null;
        $search = $request->string('q')->value() ?: null;

        if ($search !== null) {
            $this->activity->log('search', properties: ['q' => $search]);
        }

        // Themed sections only on the plain home (no category/search filter).
        $sections = [];
        if ($category === null && $search === null) {
            $featured = $this->events->featured();
            $weekend = $this->events->thisWeekend();
            if ($featured->isNotEmpty()) {
                $sections[] = ['key' => 'featured', 'title' => 'Curadoria VAGA', 'events' => $featured];
            }
            if ($weekend->isNotEmpty()) {
                $sections[] = ['key' => 'weekend', 'title' => 'Este fim de semana', 'events' => $weekend];
            }
        }

        return view('events.index', [
            'events' => $this->events->upcomingList($category, $search),
            'category' => $category,
            'search' => $search,
            'heading' => null,
            'categories' => Category::query()->where('is_active', true)->orderBy('position')->get(),
            'sections' => $sections,
        ]);
    }

    /** Returns just the events grid (for dynamic category filtering, no reload). */
    public function grid(Request $request): View
    {
        $category = $request->string('category')->value() ?: null;

        return view('partials.events-grid', [
            'events' => $this->events->upcomingList($category),
            'category' => $category,
        ]);
    }

    public function category(Category $category): View
    {
        return view('events.index', [
            'events' => $this->events->upcomingList($category->slug),
            'category' => $category->slug,
            'search' => null,
            'heading' => $category->name,
        ]);
    }

    public function tag(Tag $tag): View
    {
        return view('events.index', [
            'events' => $this->events->forTag($tag),
            'category' => null,
            'search' => null,
            'heading' => '#' . $tag->name,
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

        $this->activity->log('event.view', $event);

        $event->load([
            'categories',
            'tags',
            'ticketTiers',
            'promoter.organization',
            'images',
            'occurrences' => fn ($q) => $q->orderBy('starts_at')->with(['venue', 'ticketTypes']),
        ]);

        return view('events.show', ['event' => $event]);
    }
}
