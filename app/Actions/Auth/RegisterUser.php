<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Support\Facades\DB;

/**
 * Creates a new registered user and assigns the default role.
 *
 * Single responsibility: user registration. Reused by the API and (later)
 * the web session auth so the registration rules live in exactly one place.
 */
final class RegisterUser
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data, UserRole $role = UserRole::User): User
    {
        return DB::transaction(function () use ($data, $role): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'role' => $role->value,
            ]);

            // refresh() so database defaults (e.g. locale) are reflected in the response.
            return $user->refresh()->load('roles');
        });
    }
}
