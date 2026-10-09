<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\Impersonation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function __construct(private readonly Impersonation $impersonation) {}

    public function start(Request $request, User $user): RedirectResponse
    {
        try {
            $this->impersonation->start($request, $request->user(), $user);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['impersonate' => $e->getMessage()]);
        }

        return redirect()->route('painel.eventos.index')
            ->with('status', "Estás a ver a plataforma como {$user->name}.");
    }

    public function stop(Request $request): RedirectResponse
    {
        $this->impersonation->stop($request);

        return redirect()->route('admin.users.index')->with('status', 'Voltaste à tua conta de administrador.');
    }
}
