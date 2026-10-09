<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\PromoterRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PromoterRequest;
use App\Services\Promoters\PromoterOnboarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoterRequestController extends Controller
{
    public function __construct(private readonly PromoterOnboarding $onboarding) {}

    public function index(): View
    {
        return view('admin.promoter-requests.index', [
            'requests' => PromoterRequest::query()
                ->where('status', PromoterRequestStatus::Pending->value)
                ->with('user')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function approve(Request $request, PromoterRequest $promoterRequest): RedirectResponse
    {
        $promoter = $this->onboarding->approve($promoterRequest, $request->user());

        return back()->with('status', "Promotor \"{$promoter->name}\" criado e papel atribuído.");
    }

    public function reject(Request $request, PromoterRequest $promoterRequest): RedirectResponse
    {
        $this->onboarding->reject($promoterRequest, $request->user(), $request->input('notes'));

        return back()->with('status', 'Candidatura recusada.');
    }
}
