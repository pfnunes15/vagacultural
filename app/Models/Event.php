<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgeRating;
use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

/**
 * @property EventStatus $status
 * @property AgeRating|null $min_age
 * @property Carbon|null $published_at
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasTranslations, Searchable, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'summary', 'description'];

    protected $fillable = [
        'promoter_id', 'submitted_by',
        'title', 'slug', 'summary', 'description', 'status', 'is_featured',
        'cover_image_path', 'is_free', 'price_from', 'ticket_url',
        'website', 'min_age', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'min_age' => AgeRating::class,
            'is_featured' => 'boolean',
            'is_free' => 'boolean',
            'price_from' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Promoter, $this> */
    public function promoter(): BelongsTo
    {
        return $this->belongsTo(Promoter::class);
    }

    /**
     * The organization behind this event, reached through its promoter.
     * (Events are never linked to an organization directly.)
     */
    public function organization(): ?Organization
    {
        return $this->promoter?->organization;
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category')->withPivot('is_primary');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'event_tag');
    }

    /** @return HasMany<EventTicketTier, $this> */
    public function ticketTiers(): HasMany
    {
        return $this->hasMany(EventTicketTier::class)->orderBy('position');
    }

    /** @return HasMany<EventOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(EventOccurrence::class);
    }

    /** @return HasMany<EventImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('position');
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Data indexed in the search engine. Translatable fields are flattened
     * across all locales so a query in any language matches.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $flatten = fn (array $translations): string => trim(implode(' ', array_filter(array_values($translations))));

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $flatten($this->getTranslations('title')),
            'summary' => $flatten($this->getTranslations('summary')),
            'description' => $flatten($this->getTranslations('description')),
            'categories' => $this->categories->flatMap(fn ($c) => array_values($c->getTranslations('name')))->implode(' '),
            'tags' => $this->tags->pluck('name')->implode(' '),
            'promoter' => $this->promoter?->name,
            'venues' => $this->occurrences->map(fn ($o) => $o->venue?->name)->filter()->implode(' '),
            'next_occurrence' => $this->occurrences->min('starts_at')?->timestamp,
        ];
    }

    /** Only publicly visible events are indexed. */
    public function shouldBeSearchable(): bool
    {
        return $this->status === EventStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
