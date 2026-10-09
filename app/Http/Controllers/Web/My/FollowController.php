<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Promoter;
use App\Services\Engagement\FollowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(private readonly FollowService $follows) {}

    public function promoter(Request $request, Promoter $promoter): RedirectResponse
    {
        $now = $this->follows->toggle($request->user(), $promoter);

        return back()->with('status', $now ? 'Começaste a seguir este promotor.' : 'Deixaste de seguir.');
    }

    public function organization(Request $request, Organization $organization): RedirectResponse
    {
        $now = $this->follows->toggle($request->user(), $organization);

        return back()->with('status', $now ? 'Começaste a seguir esta organização.' : 'Deixaste de seguir.');
    }
}
