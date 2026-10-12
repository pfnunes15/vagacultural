<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\TicketOrder;
use App\Services\Ticketing\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function show(Request $request, TicketOrder $order): View
    {
        $this->authorizeOrder($request, $order);
        abort_unless($order->payment_status === PaymentStatus::Pending, 404);

        return view('my.checkout', [
            'order' => $order->load(['tickets.type', 'occurrence.event']),
            // Real payment happens on Stripe; in the fake gateway we simulate it here.
            'simulated' => config('ticketing.gateway') === 'fake',
        ]);
    }

    /**
     * Simulated payment confirmation — only available with the fake gateway.
     * In production the Stripe webhook calls PaymentService::markPaid().
     */
    public function pay(Request $request, TicketOrder $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);
        abort_unless(config('ticketing.gateway') === 'fake', 404);
        abort_unless($order->payment_status === PaymentStatus::Pending, 404);

        $this->payments->markPaid((string) $order->payment_intent_id);

        return redirect()->route('my.tickets')->with('status', 'Pagamento confirmado! Os teus bilhetes estão disponíveis.');
    }

    public function cancel(Request $request, TicketOrder $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->payment_status === PaymentStatus::Pending) {
            $this->payments->expire($order);
        }

        return redirect()->route('events.index')->with('status', 'Compra cancelada.');
    }

    private function authorizeOrder(Request $request, TicketOrder $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
