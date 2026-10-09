<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Event;

use App\Enums\AgeRating;
use App\Models\Event;
use App\Support\CoverImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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

            // Cover image must match Instagram portrait (4:5) exactly.
            'cover_image' => [
                'nullable',
                File::image()->dimensions(
                    Rule::dimensions()->width(CoverImage::WIDTH)->height(CoverImage::HEIGHT),
                ),
            ],

            'min_age' => ['nullable', Rule::in(AgeRating::values())],

            // Categories (one or more) + free-text tags.
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],

            // Ticketing info (no engine yet): free, or one or more priced tiers by age.
            'is_free' => ['sometimes', 'boolean'],
            'ticket_url' => ['nullable', 'url', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'tiers' => ['array'],
            'tiers.*.name' => ['required_with:tiers.*.price', 'string', 'max:120'],
            'tiers.*.price' => ['nullable', 'numeric', 'min:0'],
            'tiers.*.min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'tiers.*.max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:tiers.*.min_age'],

            // Dates: one or more occurrences; each single, part of several, or a continuous range.
            'occurrences' => ['required', 'array', 'min:1'],
            'occurrences.*.starts_at' => ['required', 'date'],
            'occurrences.*.ends_at' => ['nullable', 'date', 'after_or_equal:occurrences.*.starts_at'],
            'occurrences.*.is_all_day' => ['sometimes', 'boolean'],

            // Location: online (link) OR a venue OR a one-off address + postal code.
            'occurrences.*.is_online' => ['sometimes', 'boolean'],
            'occurrences.*.online_url' => ['nullable', 'required_if:occurrences.*.is_online,1', 'url', 'max:255'],
            'occurrences.*.venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'occurrences.*.address' => ['nullable', 'string', 'max:255'],
            'occurrences.*.postal_code' => ['nullable', 'string', 'max:16'],
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
            'min_age' => $this->filled('min_age') ? (int) $this->input('min_age') : null,
            'is_free' => $this->boolean('is_free'),
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
     * @return list<string>
     */
    public function tagNames(): array
    {
        $raw = (string) $this->input('tags', '');

        return array_values(array_filter(array_map('trim', explode(',', $raw)), fn (string $t): bool => $t !== ''));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function occurrences(): array
    {
        return array_values($this->input('occurrences', []));
    }

    /**
     * Priced tiers, discarding empty rows.
     *
     * @return list<array<string, mixed>>
     */
    public function ticketTiers(): array
    {
        return array_values(array_filter(
            $this->input('tiers', []),
            fn (array $tier): bool => isset($tier['name']) && trim((string) $tier['name']) !== '',
        ));
    }
}
