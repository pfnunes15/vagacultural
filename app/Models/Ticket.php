<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property TicketStatus $status
 * @property Carbon|null $checked_in_at
 */
class Ticket extends Model
{
    protected $fillable = [
        'ticket_order_id', 'ticket_type_id', 'code',
        'attendee_name', 'status', 'checked_in_at', 'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'checked_in_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<TicketOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    /** @return BelongsTo<TicketType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
