<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Event;

use App\Models\Event;

/**
 * Same fields as StoreEventRequest, minus the promoter (ownership is fixed on
 * edit). Authorization is the update policy on the bound event.
 */
class UpdateEventRequest extends StoreEventRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && ($this->user()?->can('update', $event) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['promoter_id']);

        return $rules;
    }
}
