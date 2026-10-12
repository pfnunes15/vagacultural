<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EventOccurrence;
use App\Models\Ticket;
use App\Models\User;

/**
 * Who can manage ticketing for an occurrence / check tickets in: the owner
 * promoter, the managing organization, or an admin — same rule as editing
 * the underlying event.
 */
class TicketTypePolicy
{
    public function manage(User $user, EventOccurrence $occurrence): bool
    {
        $event = $occurrence->event;

        if ($event === null) {
            return false;
        }

        return $user->can('update', $event);
    }

    public function checkIn(User $user, Ticket $ticket): bool
    {
        $occurrence = $ticket->order->occurrence;

        return $occurrence !== null && $this->manage($user, $occurrence);
    }
}
