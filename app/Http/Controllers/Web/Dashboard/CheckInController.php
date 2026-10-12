<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EventOccurrence;
use App\Services\Ticketing\TicketingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(private readonly TicketingService $ticketing) {}

    public function show(EventOccurrence $occurrence): View
    {
        $this->authorize('manage', $occurrence);

        return view('dashboard.check-in', [
            'occurrence' => $occurrence->load('event'),
            'result' => null,
        ]);
    }

    public function scan(Request $request, EventOccurrence $occurrence): RedirectResponse
    {
        $this->authorize('manage', $occurrence);
        $code = trim((string) $request->input('code'));

        try {
            $outcome = $this->ticketing->checkIn($code, $request->user());
        } catch (\RuntimeException $e) {
            return back()->with('checkin', ['status' => 'not_found', 'message' => $e->getMessage()]);
        }

        // Ensure the ticket belongs to this occurrence.
        if ($outcome['ticket']->order->event_occurrence_id !== $occurrence->id) {
            return back()->with('checkin', ['status' => 'wrong_event', 'message' => 'Bilhete de outra sessão.']);
        }

        $messages = [
            'ok' => 'Entrada validada.',
            'already_used' => 'Este bilhete já tinha sido usado.',
            'cancelled' => 'Bilhete cancelado.',
        ];

        return back()->with('checkin', [
            'status' => $outcome['status'],
            'message' => $messages[$outcome['status']] ?? 'Verificado.',
        ]);
    }
}
