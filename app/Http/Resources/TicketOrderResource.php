<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TicketOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TicketOrder */
class TicketOrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status->value,
            'total' => (float) $this->total,
            'event' => $this->whenLoaded('occurrence', fn () => $this->occurrence->event ? [
                'title' => $this->occurrence->event->title,
                'slug' => $this->occurrence->event->slug,
                'starts_at' => $this->occurrence->starts_at->toIso8601String(),
            ] : null),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
        ];
    }
}
