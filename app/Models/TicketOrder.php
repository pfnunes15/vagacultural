<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TicketOrderStatus $status
 */
class TicketOrder extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'event_occurrence_id', 'status', 'total',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketOrderStatus::class,
            'total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
