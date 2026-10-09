<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

/**
 * NOTE: provisional field set — to be extended once the final list of event
 * fields is defined. The publication workflow does not depend on these fields.
 */
class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'promoter_id' => ['required', 'integer', 'exists:promoters,id'],
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_free' => ['sometimes', 'boolean'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'required_if:is_free,false'],
            'ticket_url' => ['nullable', 'url', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'categories' => ['array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'occurrences' => ['required', 'array', 'min:1'],
            'occurrences.*.starts_at' => ['required', 'date'],
            'occurrences.*.ends_at' => ['nullable', 'date', 'after:occurrences.*.starts_at'],
            'occurrences.*.venue_id' => ['nullable', 'integer', 'exists:venues,id'],
        ];
    }

    /**
     * Event attributes to persist (relations handled separately).
     *
     * @return array<string, mixed>
     */
    public function eventAttributes(): array
    {
        return [
            'title' => $this->string('title')->value(),
            'summary' => $this->input('summary'),
            'description' => $this->input('description'),
            'is_free' => $this->boolean('is_free'),
            'price_from' => $this->boolean('is_free') ? null : $this->input('price_from'),
            'ticket_url' => $this->input('ticket_url'),
            'website' => $this->input('website'),
        ];
    }

    /**
     * @return list<int>
     */
    public function categoryIds(): array
    {
        return array_map('intval', $this->input('categories', []));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function occurrences(): array
    {
        return array_values($this->input('occurrences', []));
    }
}
