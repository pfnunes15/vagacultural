<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Lets an admin browse the site as a promoter or organization.
 * The original admin id is kept in the session so they can return.
 */
class Impersonation
{
    private const KEY = 'impersonator_id';

    public function start(Request $request, User $admin, User $target): void
    {
        if (! $admin->isAdmin()) {
            throw new RuntimeException('Apenas administradores podem impersonar.');
        }

        if ($target->isAdmin()) {
            throw new RuntimeException('Não é possível impersonar outro administrador.');
        }

        if ($this->isImpersonating($request)) {
            throw new RuntimeException('Já estás a impersonar um utilizador.');
        }

        $request->session()->put(self::KEY, $admin->id);
        Auth::login($target);
    }

    public function stop(Request $request): void
    {
        $adminId = $request->session()->pull(self::KEY);

        if ($adminId === null) {
            return;
        }

        $admin = User::find($adminId);

        if ($admin !== null) {
            Auth::login($admin);
        } else {
            Auth::logout();
        }
    }

    public function isImpersonating(Request $request): bool
    {
        return $request->session()->has(self::KEY);
    }

    public function impersonatorId(Request $request): ?int
    {
        /** @var int|null $id */
        $id = $request->session()->get(self::KEY);

        return $id;
    }
}
