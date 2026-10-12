<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TicketOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property TicketOrderStatus $status
 * @property PaymentStatus $payment_status
 * @property Carbon|null $reserved_until
 */
class TicketOrder extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'event_occurrence_id', 'status', 'total',
        'commission_amount', 'currency', 'payment_status', 'payment_intent_id',
        'reserved_until', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketOrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'total' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'reserved_until' => 'datetime',
            'paid_at' => 'datetime',
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
