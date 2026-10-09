<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Promoter;
use App\Services\Events\PublicEventService;
use Illuminate\View\View;

class PromoterController extends Controller
{
    public function __construct(private readonly PublicEventService $events) {}

    public function index(): View
    {
        return view('promoters.index', [
            'promoters' => Promoter::query()->where('is_active', true)->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Promoter $promoter): View
    {
        abort_unless($promoter->is_active, 404);

        return view('promoters.show', [
            'promoter' => $promoter->load('organization'),
            'events' => $this->events->forPromoter($promoter),
        ]);
    }
}
