@extends('layouts.app')
@section('title', 'Redefinir palavra-passe — VAGA')
@section('content')
    <h1>Redefinir palavra-passe</h1>
    <form method="POST" action="{{ route('password.store') }}" style="max-width:420px">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required>
        @error('email')<div class="err">{{ $message }}</div>@enderror
        <label for="password">Nova palavra-passe</label>
        <input id="password" name="password" type="password" required>
        @error('password')<div class="err">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirmar palavra-passe</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
        <p style="margin-top:16px"><button type="submit">Redefinir</button></p>
    </form>
@endsection
