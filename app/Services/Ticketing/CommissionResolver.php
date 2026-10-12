<?php

declare(strict_types=1);

namespace App\Services\Ticketing;

use App\Models\Event;
use App\Models\Promoter;

/**
 * Resolves the platform commission rate (%) for an event, in cascade:
 * event override -> promoter default -> platform default.
 */
class CommissionResolver
{
    public function rateForEvent(Event $event): float
    {
        if ($event->commission_rate !== null) {
            return (float) $event->commission_rate;
        }

        return $this->rateForPromoter($event->promoter);
    }

    public function rateForPromoter(?Promoter $promoter): float
    {
        if ($promoter?->commission_rate !== null) {
            return (float) $promoter->commission_rate;
        }

        return (float) config('ticketing.default_commission_rate');
    }

    /** Commission amount (money) for a given total and event. */
    public function amount(Event $event, float $total): float
    {
        return round($total * $this->rateForEvent($event) / 100, 2);
    }
}
