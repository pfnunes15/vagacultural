<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\UpdateRolesRequest;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\Mail\RegistrationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->string('q')->value();

        $users = User::query()
            ->when($term !== '', fn ($q) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"),
            ))
            ->with(['roles', 'promoterProfile'])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'allRoles' => UserRole::cases(),
            'term' => $term,
        ]);
    }

    /** Assign/sync a user's roles. An admin may not strip their own admin role. */
    public function updateRoles(UpdateRolesRequest $request, User $user): RedirectResponse
    {
        $roles = $request->roles();

        if ($user->is($request->user()) && ! in_array(UserRole::Admin->value, $roles, true)) {
            return back()->withErrors(['roles' => 'Não podes remover o teu próprio papel de administrador.']);
        }

        $user->roles()->delete();
        foreach ($roles as $role) {
            UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role]);
        }

        return back()->with('status', "Papéis de {$user->name} atualizados.");
    }

    public function resendEmail(Request $request, User $user, RegistrationMailer $mailer): RedirectResponse
    {
        $mailer->send($user, $request->user());

        return back()->with('status', "E-mail de registo reenviado para {$user->email}.");
    }

    /** Toggle the admin-controlled publishing trust of a user's promoter. */
    public function toggleTrust(User $user): RedirectResponse
    {
        $promoter = $user->promoterProfile;

        if ($promoter === null) {
            return back()->withErrors(['trust' => 'Este utilizador não tem perfil de promotor.']);
        }

        $promoter->update(['auto_publish' => ! $promoter->auto_publish]);

        $state = $promoter->auto_publish ? 'ativada' : 'desativada';

        return back()->with('status', "Publicação automática {$state} para {$promoter->name}.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $promoter = $user->promoterProfile;

        if ($promoter !== null) {
            $promoter->update(['is_active' => ! $promoter->is_active]);
        }

        return back()->with('status', "Estado de {$user->name} atualizado.");
    }
}
