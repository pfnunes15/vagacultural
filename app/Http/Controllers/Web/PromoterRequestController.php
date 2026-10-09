<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Promoter\PromoterRequestRequest;
use App\Services\Promoters\PromoterOnboarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoterRequestController extends Controller
{
    public function __construct(private readonly PromoterOnboarding $onboarding) {}

    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isPromoter()) {
            return redirect()->route('dashboard.events.index');
        }

        return view('promoter-request.create', [
            'pending' => $this->onboarding->hasPendingRequest($user),
        ]);
    }

    public function store(PromoterRequestRequest $request): RedirectResponse
    {
        try {
            $this->onboarding->requestFor($request->user(), $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['proposed_name' => $e->getMessage()])->withInput();
        }

        return redirect()->route('events.index')
            ->with('status', 'Candidatura enviada! Entraremos em contacto após a revisão.');
    }
}
