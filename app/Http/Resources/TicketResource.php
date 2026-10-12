<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ticket */
class TicketResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status->value,
            'type' => $this->whenLoaded('type', fn () => $this->type->name),
            'attendee_name' => $this->attendee_name,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
        ];
    }
}
