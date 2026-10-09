<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EventOccurrence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventOccurrence */
class EventOccurrenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_all_day' => $this->is_all_day,
            'status' => $this->status->value,
            'location' => [
                'label' => $this->locationLabel(),
                'is_online' => $this->is_online,
                'online_url' => $this->online_url,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'venue' => $this->whenLoaded('venue', fn () => $this->venue ? [
                    'id' => $this->venue->id,
                    'name' => $this->venue->name,
                    'slug' => $this->venue->slug,
                    'municipality' => $this->venue->municipality,
                    'latitude' => $this->venue->latitude,
                    'longitude' => $this->venue->longitude,
                ] : null),
            ],
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event->id,
                'slug' => $this->event->slug,
                'title' => $this->event->title,
                'categories' => $this->event->relationLoaded('categories')
                    ? CategoryResource::collection($this->event->categories)
                    : [],
            ]),
        ];
    }
}
