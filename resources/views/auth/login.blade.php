@extends('layouts.app')
@section('title', 'Entrar — VAGA')
@section('content')
    <h1>Entrar</h1>
    @if (\Illuminate\Support\Str::contains((string) session('url.intended'), '/evento/'))
        <div class="notice">Cria conta ou entra (é grátis) para veres os detalhes do evento.</div>
    @endif
    <form method="POST" action="{{ route('login') }}" style="max-width:420px">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="err">{{ $message }}</div>@enderror
        <label for="password">Palavra-passe</label>
        <input id="password" name="password" type="password" required>
        @error('password')<div class="err">{{ $message }}</div>@enderror
        <label style="font-weight:400;display:flex;gap:8px;align-items:center;margin-top:12px">
            <input type="checkbox" name="remember" style="width:auto"> Manter sessão iniciada
        </label>
        <p style="margin-top:16px"><button type="submit">Entrar</button></p>
    </form>
    <p class="muted">Ainda não tens conta? <a href="{{ route('register') }}">Regista-te</a>.</p>
@endsection
