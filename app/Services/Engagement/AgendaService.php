<?php

declare(strict_types=1);

namespace App\Services\Engagement;

use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\User;

/**
 * The user's personal agenda: events (optionally a specific occurrence) they
 * plan to attend.
 */
class AgendaService
{
    public function add(User $user, Event $event, ?EventOccurrence $occurrence = null): AgendaItem
    {
        return AgendaItem::firstOrCreate([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_occurrence_id' => $occurrence?->id,
        ]);
    }

    public function remove(User $user, Event $event, ?EventOccurrence $occurrence = null): void
    {
        $user->agendaItems()
            ->where('event_id', $event->id)
            ->where('event_occurrence_id', $occurrence?->id)
            ->delete();
    }

    public function has(User $user, Event $event): bool
    {
        return $user->agendaItems()->where('event_id', $event->id)->exists();
    }
}
