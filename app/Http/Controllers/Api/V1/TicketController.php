<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketOrderResource;
use App\Http\Resources\TicketTypeResource;
use App\Models\EventOccurrence;
use App\Models\Ticket;
use App\Services\Ticketing\TicketingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function __construct(private readonly TicketingService $ticketing) {}

    /** Ticket types available for an occurrence. */
    public function types(EventOccurrence $occurrence): AnonymousResourceCollection
    {
        return TicketTypeResource::collection(
            $occurrence->ticketTypes()->where('is_active', true)->get(),
        );
    }

    /** Register the authenticated user for a session. */
    public function register(Request $request, EventOccurrence $occurrence): JsonResponse
    {
        /** @var array<int, int> $quantities */
        $quantities = array_map('intval', (array) $request->input('quantities', []));

        try {
            $order = $this->ticketing->register($request->user(), $quantities);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new TicketOrderResource($order->load(['tickets.type', 'occurrence.event'])))
            ->response()->setStatusCode(201);
    }

    /** The authenticated user's confirmed ticket orders. */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->ticketOrders()
            ->where('status', 'confirmed')
            ->with(['tickets.type', 'occurrence.event'])
            ->latest()
            ->paginate(20);

        return TicketOrderResource::collection($orders);
    }

    /** Check a ticket in at the door (promoter/organization/admin). */
    public function checkIn(Request $request, string $code): JsonResponse
    {
        $ticket = Ticket::where('code', $code)->first();

        if ($ticket === null) {
            return response()->json(['status' => 'not_found', 'message' => 'Bilhete não encontrado.'], 404);
        }

        if ($request->user()->cannot('checkIn', $ticket)) {
            abort(403);
        }

        $outcome = $this->ticketing->checkIn($code, $request->user());

        return response()->json([
            'status' => $outcome['status'],
            'attendee' => $outcome['ticket']->attendee_name,
        ]);
    }
}
