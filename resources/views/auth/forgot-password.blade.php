@extends('layouts.app')
@section('title', 'Recuperar palavra-passe — VAGA')
@section('content')
    <div class="max-w-md mx-auto vcard p-7 mt-6">
        <h1 class="font-display text-2xl font-bold">Recuperar palavra-passe</h1>
        <p class="text-[var(--color-muted)] text-sm mt-1">Indica o teu email e enviamos um link para redefinir.</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-4">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="err">{{ $message }}</div>@enderror
            <button type="submit" class="btn-primary w-full mt-5">Enviar link</button>
        </form>
    </div>
@endsection
