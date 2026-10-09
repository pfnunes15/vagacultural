<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UnsubscribeController extends Controller
{
    public function __invoke(User $user): RedirectResponse
    {
        $user->update(['marketing_emails' => false]);

        return redirect()->route('events.index')
            ->with('status', 'Deixaste de receber os nossos e-mails. Podes reativar no teu perfil.');
    }
}
