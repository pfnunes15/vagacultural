<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\PasswordUpdateRequest;
use App\Http\Requests\Web\Profile\ProfileUpdateRequest;
use App\Models\Category;
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

        return view('my.profile', [
            'user' => $user,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'favoriteCategoryIds' => $user->favorites()
                ->where('favoritable_type', (new Category)->getMorphClass())
                ->pluck('favoritable_id')->all(),
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
