<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Ticketing\TicketTypeRequest;
use App\Models\EventOccurrence;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(EventOccurrence $occurrence): View
    {
        $this->authorize('manage', $occurrence);

        return view('dashboard.ticket-types.index', [
            'occurrence' => $occurrence->load('event', 'ticketTypes'),
        ]);
    }

    public function store(TicketTypeRequest $request, EventOccurrence $occurrence): RedirectResponse
    {
        $this->authorize('manage', $occurrence);

        $occurrence->ticketTypes()->create([
            ...$request->safe()->only(['name', 'price', 'capacity', 'sales_start_at', 'sales_end_at']),
            'is_active' => $request->boolean('is_active', true),
            'position' => (int) $occurrence->ticketTypes()->max('position') + 1,
        ]);

        return back()->with('status', 'Tipo de bilhete criado.');
    }

    public function update(TicketTypeRequest $request, EventOccurrence $occurrence, TicketType $ticketType): RedirectResponse
    {
        $this->authorize('manage', $occurrence);
        abort_unless($ticketType->event_occurrence_id === $occurrence->id, 404);

        $ticketType->update([
            ...$request->safe()->only(['name', 'price', 'capacity', 'sales_start_at', 'sales_end_at']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Tipo de bilhete atualizado.');
    }

    public function destroy(EventOccurrence $occurrence, TicketType $ticketType): RedirectResponse
    {
        $this->authorize('manage', $occurrence);
        abort_unless($ticketType->event_occurrence_id === $occurrence->id, 404);

        if ($ticketType->tickets()->where('status', '!=', 'cancelled')->exists()) {
            return back()->withErrors(['ticket_type' => 'Não podes remover um tipo com bilhetes emitidos.']);
        }

        $ticketType->delete();

        return back()->with('status', 'Tipo de bilhete removido.');
    }
}
