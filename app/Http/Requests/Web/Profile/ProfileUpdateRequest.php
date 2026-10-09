<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'nationality' => ['nullable', 'string', 'size:2'],
            'locale' => ['nullable', Rule::in(array_keys(config('locales.supported')))],
            'marketing_emails' => ['sometimes', 'boolean'],
            'categories' => ['array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
