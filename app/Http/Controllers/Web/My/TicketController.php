<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\EventOccurrence;
use App\Services\Ticketing\TicketingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private readonly TicketingService $ticketing) {}

    public function index(Request $request): View
    {
        $orders = $request->user()->ticketOrders()
            ->where('status', 'confirmed')
            ->with(['tickets.type', 'occurrence.event', 'occurrence.venue'])
            ->latest()
            ->get();

        return view('my.tickets', ['orders' => $orders]);
    }

    public function store(Request $request, EventOccurrence $occurrence): RedirectResponse
    {
        /** @var array<int, int> $quantities */
        $quantities = array_map('intval', (array) $request->input('quantities', []));

        try {
            $this->ticketing->register($request->user(), $quantities);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['tickets' => $e->getMessage()]);
        }

        return redirect()->route('my.tickets')->with('status', 'Inscrição confirmada! Os teus bilhetes estão disponíveis.');
    }
}
