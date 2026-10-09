<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Engagement\AgendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function __construct(private readonly AgendaService $agenda) {}

    public function index(Request $request): View
    {
        $items = $request->user()->agendaItems()
            ->with(['event.categories', 'occurrence.venue'])
            ->get()
            ->filter(fn ($item) => $item->event !== null);

        return view('my.agenda', ['items' => $items]);
    }

    public function toggle(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if ($this->agenda->has($user, $event)) {
            $this->agenda->remove($user, $event);

            return back()->with('status', 'Removido da tua agenda.');
        }

        $this->agenda->add($user, $event);

        return back()->with('status', 'Adicionado à tua agenda.');
    }
}
