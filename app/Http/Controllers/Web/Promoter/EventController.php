<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Promoter;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Event\StoreEventRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\Venue;
use App\Services\Events\EventWorkflow;
use App\Services\Promoters\PromoterAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly PromoterAccess $access,
        private readonly EventWorkflow $workflow,
    ) {}

    /** The events the current account owns or manages. */
    public function index(): View
    {
        $user = request()->user();
        $postable = $this->access->postableBy($user);

        $events = Event::query()
            ->whereIn('promoter_id', $postable->pluck('id'))
            ->with(['promoter', 'categories'])
            ->latest()
            ->paginate(20);

        return view('promoter.events.index', [
            'events' => $events,
            'statuses' => EventStatus::class,
            // An organization with no associated promoter cannot create events.
            'canCreate' => $user->can('create', Event::class),
            'needsPromoter' => $user->isOrganization() && $postable->isEmpty(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('promoter.events.create', [
            'promoters' => $this->access->postableBy(request()->user()),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'venues' => Venue::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $promoter = Promoter::findOrFail($request->integer('promoter_id'));

        if (! $this->access->canPostAs($request->user(), $promoter)) {
            throw ValidationException::withMessages([
                'promoter_id' => 'Não podes publicar em nome deste promotor.',
            ]);
        }

        $event = $this->workflow->submit(
            $promoter,
            $request->eventAttributes(),
            $request->categoryIds(),
            $request->occurrences(),
            $request->user(),
        );

        $message = $event->status === EventStatus::Published
            ? 'Evento publicado! Já está visível na agenda.'
            : 'Evento submetido. Ficará visível após aprovação de um administrador.';

        return redirect()->route('painel.eventos.index')->with('status', $message);
    }
}
