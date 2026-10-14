<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\PasswordUpdateRequest;
use App\Http\Requests\Web\Profile\ProfileUpdateRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\Tag;
use App\Services\Engagement\FavoriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly FavoriteService $favorites) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        $followedPromoters = Promoter::query()
            ->whereIn('id', $user->follows()
                ->where('followable_type', (new Promoter)->getMorphClass())
                ->pluck('followable_id'))
            ->withCount('events')
            ->orderBy('name')
            ->get();

        $favoriteEvents = Event::query()
            ->whereIn('id', $user->favorites()
                ->where('favoritable_type', (new Event)->getMorphClass())
                ->pluck('favoritable_id'))
            ->published()
            ->with(['categories', 'occurrences' => fn ($q) => $q->upcoming()->with('venue')])
            ->limit(3)
            ->get();

        return view('my.profile', [
            'user' => $user,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'favoriteCategoryIds' => $user->favorites()
                ->where('favoritable_type', (new Category)->getMorphClass())
                ->pluck('favoritable_id')->all(),
            'tags' => Tag::query()->withCount('events')->orderByDesc('events_count')->orderBy('name')->limit(30)->get(),
            'favoriteTagIds' => $user->favorites()
                ->where('favoritable_type', (new Tag)->getMorphClass())
                ->pluck('favoritable_id')->all(),
            'followedPromoters' => $followedPromoters,
            'favoriteEvents' => $favoriteEvents,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->only(['first_name', 'last_name', 'nationality', 'locale']);
        $data['marketing_emails'] = $request->boolean('marketing_emails');

        $emailChanged = $request->input('email') !== $user->email;
        $user->fill($data);
        $user->email = $request->string('email')->value();

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->favorites->syncCategories($user, array_map('intval', $request->input('categories', [])));
        $this->favorites->syncTags($user, array_map('intval', $request->input('tags', [])));

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Perfil atualizado.');
    }

    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->string('password')->value()]);

        return back()->with('status', 'Palavra-passe alterada.');
    }
}
