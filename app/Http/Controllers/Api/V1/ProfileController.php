<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roles'));
    }

    public function update(Request $request): UserResource
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:80'],
            'last_name' => ['sometimes', 'required', 'string', 'max:80'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nationality' => ['nullable', 'string', 'size:2'],
            'locale' => ['nullable', Rule::in(array_keys(config('locales.supported')))],
        ]);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill($data)->save();

        return new UserResource($user->load('roles'));
    }
}
