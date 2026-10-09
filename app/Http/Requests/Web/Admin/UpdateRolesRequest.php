<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolesRequest extends FormRequest
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
        return [
            'roles' => ['array'],
            'roles.*' => [Rule::enum(UserRole::class)],
        ];
    }

    /**
     * @return list<string>
     */
    public function roles(): array
    {
        /** @var list<string> $roles */
        $roles = array_values(array_unique($this->input('roles', [])));

        return $roles;
    }
}
