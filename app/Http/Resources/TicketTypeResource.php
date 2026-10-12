<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TicketType */
class TicketTypeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'is_free' => $this->isFree(),
            'remaining' => $this->remaining(),
            'on_sale' => $this->onSale(),
        ];
    }
}
