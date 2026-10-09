<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Events\EventWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventModerationController extends Controller
{
    public function __construct(private readonly EventWorkflow $workflow) {}

    /** Queue of events awaiting approval. */
    public function index(): View
    {
        $this->authorize('moderate', Event::class);

        return view('admin.events.pending', [
            'events' => Event::query()
                ->where('status', EventStatus::Pending->value)
                ->with(['promoter.organization', 'categories'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function approve(Event $event): RedirectResponse
    {
        $this->authorize('moderate', Event::class);
        $this->workflow->approve($event);

        return back()->with('status', "Evento \"{$event->title}\" publicado.");
    }

    public function reject(Event $event): RedirectResponse
    {
        $this->authorize('moderate', Event::class);
        $this->workflow->reject($event);

        return back()->with('status', "Evento \"{$event->title}\" rejeitado.");
    }
}
