<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Ticketing\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketQrController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket, QrCodeGenerator $qr): Response
    {
        // Only the ticket's owner may fetch its QR.
        abort_unless($ticket->order->user_id === $request->user()->id, 403);

        return response($qr->svg($ticket->code), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
