<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard: allows the request only if the authenticated user holds at
 * least one of the given roles. Usage: ->middleware('role:admin,organization').
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $allowed = array_filter(array_map(
            static fn (string $role): ?UserRole => UserRole::tryFrom($role),
            $roles,
        ));

        foreach ($allowed as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Não tem permissão para aceder a este recurso.');
    }
}
