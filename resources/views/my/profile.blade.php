@extends('layouts.app')
@section('title', 'O meu perfil — VAGA')
@section('content')
    <h1>O meu perfil</h1>

    <form method="POST" action="{{ route('my.profile.update') }}" style="max-width:520px">
        @csrf @method('PUT')
        <label for="first_name">Primeiro nome</label>
        <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
        @error('first_name')<div class="err">{{ $message }}</div>@enderror

        <label for="last_name">Último nome</label>
        <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
        @error('last_name')<div class="err">{{ $message }}</div>@enderror

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
        @error('email')<div class="err">{{ $message }}</div>@enderror
        @unless ($user->hasVerifiedEmail())<div class="muted">Email por verificar.</div>@endunless

        <label for="nationality">Nacionalidade (código ISO, ex: PT)</label>
        <input id="nationality" name="nationality" maxlength="2" value="{{ old('nationality', $user->nationality) }}" style="text-transform:uppercase">
        @error('nationality')<div class="err">{{ $message }}</div>@enderror

        <label for="locale">Idioma preferido</label>
        <select id="locale" name="locale" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
            @foreach (config('locales.supported') as $code => $label)
                <option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>
            @endforeach
        </select>

        <label>Categorias favoritas</label>
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach ($categories as $cat)
                <label style="font-weight:400;display:flex;gap:6px;align-items:center">
                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" style="width:auto"
                        @checked(in_array($cat->id, old('categories', $favoriteCategoryIds)))> {{ $cat->name }}
                </label>
            @endforeach
        </div>

        <p style="margin-top:16px"><button type="submit">Guardar perfil</button></p>
    </form>

    <h2 style="margin-top:32px">Alterar palavra-passe</h2>
    <form method="POST" action="{{ route('my.profile.password') }}" style="max-width:420px">
        @csrf @method('PUT')
        <label for="current_password">Palavra-passe atual</label>
        <input id="current_password" name="current_password" type="password" required>
        @error('current_password')<div class="err">{{ $message }}</div>@enderror
        <label for="password">Nova palavra-passe</label>
        <input id="password" name="password" type="password" required>
        @error('password')<div class="err">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirmar nova palavra-passe</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
        <p style="margin-top:16px"><button type="submit">Alterar palavra-passe</button></p>
    </form>
@endsection
