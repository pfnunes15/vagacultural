@extends('layouts.app')
@section('title', 'Entrar — VAGA')
@section('content')
    <div class="max-w-md mx-auto vcard p-7 mt-6">
        <h1 class="font-display text-2xl font-bold">Entrar</h1>
        @if (\Illuminate\Support\Str::contains((string) session('url.intended'), '/event/'))
            <div class="notice mt-3">Cria conta ou entra (é grátis) para veres os detalhes do evento.</div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="mt-4">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="err">{{ $message }}</div>@enderror
            <label for="password">Palavra-passe</label>
            <input id="password" name="password" type="password" required>
            @error('password')<div class="err">{{ $message }}</div>@enderror
            <label class="font-normal flex items-center gap-2 mt-3"><input type="checkbox" name="remember" class="w-auto"> Manter sessão iniciada</label>
            <button type="submit" class="btn-primary w-full mt-5">Entrar</button>
        </form>
        <p class="text-sm text-[var(--color-muted)] mt-4"><a href="{{ route('password.request') }}">Esqueci-me da palavra-passe</a></p>
        <p class="text-sm text-[var(--color-muted)]">Ainda não tens conta? <a href="{{ route('register') }}">Regista-te</a>.</p>
    </div>
@endsection
