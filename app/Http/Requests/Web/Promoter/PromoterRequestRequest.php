<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Promoter;

use Illuminate\Foundation\Http\FormRequest;

class PromoterRequestRequest extends FormRequest
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
            'proposed_name' => ['required', 'string', 'max:160'],
            'requested_type' => ['nullable', 'in:promoter,organization'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
