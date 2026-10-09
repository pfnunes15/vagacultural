<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Event */
class EventResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'description' => $this->description,
            'is_featured' => $this->is_featured,
            'cover_image_url' => $this->cover_image_path ? Storage::url($this->cover_image_path) : null,
            'min_age' => $this->min_age !== null ? [
                'value' => $this->min_age->value,
                'label' => $this->min_age->label(),
            ] : null,
            'tickets' => [
                'is_free' => $this->is_free,
                'ticket_url' => $this->ticket_url,
                'tiers' => TicketTierResource::collection($this->whenLoaded('ticketTiers')),
            ],
            'website' => $this->website,
            'published_at' => $this->published_at?->toIso8601String(),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'occurrences' => EventOccurrenceResource::collection($this->whenLoaded('occurrences')),
            'promoter' => new PromoterResource($this->whenLoaded('promoter')),
        ];
    }
}
