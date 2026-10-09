<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A priced tier of an event, optionally bound to an age range.
 * Informational only until the ticketing system is built.
 */
class EventTicketTier extends Model
{
    protected $fillable = ['event_id', 'name', 'price', 'min_age', 'max_age', 'position'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_age' => 'integer',
            'max_age' => 'integer',
            'position' => 'integer',
        ];
    }

    public function isFree(): bool
    {
        return $this->price === null || (float) $this->price === 0.0;
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
