<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $default = config('locales.default');
        $others = array_values(array_diff(array_keys(config('locales.supported')), [$default]));

        return [
            'name' => ['required', 'array'],
            'name.' . $default => ['required', 'string', 'max:80'],
            ...collect($others)->mapWithKeys(fn (string $l): array => ['name.' . $l => ['nullable', 'string', 'max:80']])->all(),
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'color' => ['nullable', 'string', 'max:9'],
            'icon' => ['nullable', 'string', 'max:64'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => array_filter($this->input('name', []), fn ($v): bool => is_string($v) && $v !== ''),
            'parent_id' => $this->input('parent_id') ?: null,
            'color' => $this->input('color'),
            'icon' => $this->input('icon'),
            'position' => (int) $this->input('position', 0),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
