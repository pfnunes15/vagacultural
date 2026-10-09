<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        $user = $registerUser->handle($request->only('first_name', 'last_name', 'email', 'password'));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/events');
    }
}
