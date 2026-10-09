<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EventTicketTier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventTicketTier */
class TicketTierResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price !== null ? (float) $this->price : null,
            'is_free' => $this->isFree(),
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
        ];
    }
}
