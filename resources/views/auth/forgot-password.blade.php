@extends('layouts.app')
@section('title', 'Recuperar palavra-passe — VAGA')
@section('content')
    <h1>Recuperar palavra-passe</h1>
    <p class="muted">Indica o teu email e enviamos-te um link para redefinir a palavra-passe.</p>
    <form method="POST" action="{{ route('password.email') }}" style="max-width:420px">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="err">{{ $message }}</div>@enderror
        <p style="margin-top:16px"><button type="submit">Enviar link</button></p>
    </form>
@endsection
