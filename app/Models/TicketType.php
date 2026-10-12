<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $sales_start_at
 * @property Carbon|null $sales_end_at
 */
class TicketType extends Model
{
    protected $fillable = [
        'event_occurrence_id', 'name', 'price', 'capacity',
        'position', 'is_active', 'sales_start_at', 'sales_end_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
            'sales_start_at' => 'datetime',
            'sales_end_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<EventOccurrence, $this> */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(EventOccurrence::class, 'event_occurrence_id');
    }

    /** @return HasMany<Ticket, $this> */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }

    /** Seats still available (capacity minus non-cancelled tickets). */
    public function remaining(): int
    {
        $taken = $this->tickets()->where('status', '!=', 'cancelled')->count();

        return max(0, $this->capacity - $taken);
    }

    public function onSale(): bool
    {
        $now = now();

        return $this->is_active
            && ($this->sales_start_at === null || $this->sales_start_at->lte($now))
            && ($this->sales_end_at === null || $this->sales_end_at->gte($now));
    }
}
