@extends('layouts.app')
@section('title', 'Criar conta — VAGA')
@section('content')
    <div class="max-w-md mx-auto vcard p-7 mt-6">
        <h1 class="font-display text-2xl font-bold">Criar conta</h1>
        <p class="text-[var(--color-muted)] text-sm mt-1">É grátis e dá acesso aos detalhes de todos os eventos.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div><label for="first_name">Primeiro nome</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus>@error('first_name')<div class="err">{{ $message }}</div>@enderror</div>
                <div><label for="last_name">Último nome</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<div class="err">{{ $message }}</div>@enderror</div>
            </div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email')<div class="err">{{ $message }}</div>@enderror
            <label for="password">Palavra-passe</label>
            <input id="password" name="password" type="password" required>
            @error('password')<div class="err">{{ $message }}</div>@enderror
            <label for="password_confirmation">Confirmar palavra-passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
            <button type="submit" class="btn-primary w-full mt-5">Criar conta</button>
        </form>
        <p class="text-sm text-[var(--color-muted)] mt-4">Já tens conta? <a href="{{ route('login') }}">Entra</a>.</p>
    </div>
@endsection
