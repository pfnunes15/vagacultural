<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Engagement\FavoriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $favorites) {}

    public function index(Request $request): View
    {
        $events = Event::query()
            ->whereIn('id', $request->user()->favorites()
                ->where('favoritable_type', (new Event)->getMorphClass())
                ->pluck('favoritable_id'))
            ->published()
            ->with(['categories', 'occurrences' => fn ($q) => $q->upcoming()->with('venue')])
            ->paginate(12);

        return view('my.favorites', ['events' => $events]);
    }

    public function toggle(Request $request, Event $event): RedirectResponse
    {
        $now = $this->favorites->toggle($request->user(), $event);

        return back()->with('status', $now ? 'Adicionado aos favoritos.' : 'Removido dos favoritos.');
    }
}
