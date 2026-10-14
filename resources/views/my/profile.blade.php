@extends('layouts.app')
@section('title', 'O meu perfil — VAGA')
@section('content')
    <div class="max-w-xl mx-auto">
        <h1 class="font-display text-3xl font-bold mb-6">O meu perfil</h1>

        <form method="POST" action="{{ route('my.profile.update') }}" class="vcard p-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div><label for="first_name">Primeiro nome</label><input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>@error('first_name')<div class="err">{{ $message }}</div>@enderror</div>
                <div><label for="last_name">Último nome</label><input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>@error('last_name')<div class="err">{{ $message }}</div>@enderror</div>
            </div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="err">{{ $message }}</div>@enderror
            @unless ($user->hasVerifiedEmail())<div class="text-sm text-[var(--color-muted)] mt-1">Email por verificar.</div>@endunless
            <div class="grid grid-cols-2 gap-3">
                <div><label for="nationality">Nacionalidade (ISO, ex: PT)</label><input id="nationality" name="nationality" maxlength="2" value="{{ old('nationality', $user->nationality) }}" class="uppercase"></div>
                <div><label for="locale">Idioma preferido</label><select id="locale" name="locale">@foreach (config('locales.supported') as $code => $label)<option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>@endforeach</select></div>
            </div>

            <label class="mt-4">Categorias favoritas</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $cat)
                    <label class="font-normal flex items-center gap-1.5 tag cursor-pointer"><input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="w-auto" @checked(in_array($cat->id, old('categories', $favoriteCategoryIds)))> {{ $cat->name }}</label>
                @endforeach
            </div>

            @if ($tags->isNotEmpty())
                <label class="mt-4">Tags favoritas</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <label class="font-normal flex items-center gap-1.5 tag cursor-pointer"><input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="w-auto" @checked(in_array($tag->id, old('tags', $favoriteTagIds)))> #{{ $tag->name }}</label>
                    @endforeach
                </div>
            @endif

            <label class="font-normal flex items-center gap-2 mt-4"><input type="checkbox" name="marketing_emails" value="1" class="w-auto" @checked(old('marketing_emails', $user->marketing_emails))> Quero receber o resumo semanal e novidades</label>
            <button type="submit" class="btn-primary mt-5">Guardar perfil</button>
        </form>

        <form method="POST" action="{{ route('my.profile.password') }}" class="vcard p-6 mt-5">
            @csrf @method('PUT')
            <h2 class="font-display text-lg font-semibold">Alterar palavra-passe</h2>
            <label for="current_password">Palavra-passe atual</label>
            <input id="current_password" name="current_password" type="password" required>
            @error('current_password')<div class="err">{{ $message }}</div>@enderror
            <label for="password">Nova palavra-passe</label>
            <input id="password" name="password" type="password" required>
            @error('password')<div class="err">{{ $message }}</div>@enderror
            <label for="password_confirmation">Confirmar nova palavra-passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
            <button type="submit" class="btn-primary mt-5">Alterar palavra-passe</button>
        </form>
    </div>
@endsection
