@extends('layouts.app')
@section('title', 'Criar conta — VAGA')
@section('content')
    <h1>Criar conta</h1>
    <p class="muted">O registo é gratuito e dá-te acesso aos detalhes de todos os eventos.</p>
    <form method="POST" action="{{ route('register') }}" style="max-width:420px">
        @csrf
        <label for="first_name">Primeiro nome</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autofocus>
        @error('first_name')<div class="err">{{ $message }}</div>@enderror

        <label for="last_name">Último nome</label>
        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required>
        @error('last_name')<div class="err">{{ $message }}</div>@enderror
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        @error('email')<div class="err">{{ $message }}</div>@enderror
        <label for="password">Palavra-passe</label>
        <input id="password" name="password" type="password" required>
        @error('password')<div class="err">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirmar palavra-passe</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
        <p style="margin-top:16px"><button type="submit">Criar conta</button></p>
    </form>
    <p class="muted">Já tens conta? <a href="{{ route('login') }}">Entra</a>.</p>
@endsection
