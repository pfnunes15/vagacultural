<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OccurrenceStatus;
use Database\Factories\EventOccurrenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $door_time
 * @property OccurrenceStatus $status
 */
class EventOccurrence extends Model
{
    /** @use HasFactory<EventOccurrenceFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id', 'venue_id', 'starts_at', 'ends_at', 'door_time',
        'status', 'notes', 'is_all_day', 'is_online', 'online_url',
        'address', 'postal_code',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'door_time' => 'datetime',
            'status' => OccurrenceStatus::class,
            'is_all_day' => 'boolean',
            'is_online' => 'boolean',
        ];
    }

    /** Human label for where this occurrence takes place. */
    public function locationLabel(): string
    {
        if ($this->is_online) {
            return 'Online';
        }

        if ($this->venue !== null) {
            return $this->venue->name;
        }

        return $this->address ?? 'Local a anunciar';
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * @param  Builder<EventOccurrence>  $query
     * @return Builder<EventOccurrence>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }
}
