<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\EventOccurrence;
use App\Models\TicketType;
use App\Services\Ticketing\PaymentService;
use App\Services\Ticketing\TicketingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketingService $ticketing,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $orders = $request->user()->ticketOrders()
            ->whereIn('payment_status', ['free', 'paid'])
            ->with(['tickets.type', 'occurrence.event', 'occurrence.venue'])
            ->latest()
            ->get();

        return view('my.tickets', ['orders' => $orders]);
    }

    public function store(Request $request, EventOccurrence $occurrence): RedirectResponse
    {
        /** @var array<int, int> $quantities */
        $quantities = array_filter(array_map('intval', (array) $request->input('quantities', [])), fn (int $q): bool => $q > 0);

        try {
            if ($this->hasPaidTypes($quantities)) {
                $result = $this->payments->startCheckout($request->user(), $quantities);

                return redirect()->route('my.checkout', $result['order']);
            }

            $this->ticketing->register($request->user(), $quantities);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['tickets' => $e->getMessage()]);
        }

        return redirect()->route('my.tickets')->with('status', 'Inscrição confirmada! Os teus bilhetes estão disponíveis.');
    }

    /**
     * @param  array<int, int>  $quantities
     */
    private function hasPaidTypes(array $quantities): bool
    {
        if ($quantities === []) {
            return false;
        }

        return TicketType::query()
            ->whereIn('id', array_keys($quantities))
            ->where('price', '>', 0)
            ->exists();
    }
}
