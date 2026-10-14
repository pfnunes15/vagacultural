@extends('layouts.app')
@section('title', 'O meu perfil — VAGA')
@section('content')
    {{-- Identity header --}}
    <section class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
        <div class="w-16 h-16 rounded-full bg-[var(--color-surface-2)] grid place-items-center font-display text-2xl font-bold shrink-0">
            {{ strtoupper(mb_substr($user->first_name ?? $user->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <h1 class="font-display text-3xl font-bold truncate">{{ $user->first_name }} {{ $user->last_name }}</h1>
            <p class="text-[var(--color-muted)] truncate">{{ $user->email }}
                @unless ($user->hasVerifiedEmail())<span class="u-label text-[var(--color-primary)] ml-1">· por verificar</span>@endunless
            </p>
            <p class="u-label mt-1">Membro desde {{ $user->created_at?->translatedFormat('F Y') }}</p>
        </div>
        <div class="sm:ml-auto flex flex-wrap gap-2">
            <a href="{{ route('my.recommendations') }}" class="btn-ghost text-sm">Para ti</a>
            <a href="{{ route('my.favorites') }}" class="btn-ghost text-sm">Guardados</a>
            <a href="{{ route('my.agenda') }}" class="btn-ghost text-sm">A minha agenda</a>
            <a href="{{ route('my.tickets') }}" class="btn-ghost text-sm">Bilhetes</a>
        </div>
    </section>

    <div class="grid lg:grid-cols-3 gap-5 items-start">
        {{-- Left: editable profile (one form, grouped sections) --}}
        <div class="lg:col-span-2 flex flex-col gap-5">
            <form method="POST" action="{{ route('my.profile.update') }}" class="vcard p-6">
                @csrf @method('PUT')

                <h2 class="font-display text-lg font-semibold">Conta</h2>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div><label for="first_name">Primeiro nome</label><input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>@error('first_name')<div class="err">{{ $message }}</div>@enderror</div>
                    <div><label for="last_name">Último nome</label><input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>@error('last_name')<div class="err">{{ $message }}</div>@enderror</div>
                </div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="err">{{ $message }}</div>@enderror
                <div class="grid grid-cols-2 gap-3">
                    <div><label for="nationality">Nacionalidade (ISO, ex: PT)</label><input id="nationality" name="nationality" maxlength="2" value="{{ old('nationality', $user->nationality) }}" class="uppercase"></div>
                    <div><label for="locale">Idioma</label><select id="locale" name="locale">@foreach (config('locales.supported') as $code => $label)<option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>@endforeach</select></div>
                </div>

                <hr class="border-[var(--color-border)] my-6">

                <h2 class="font-display text-lg font-semibold">Categorias de interesse</h2>
                <p class="text-sm text-[var(--color-muted)] mt-1 mb-3">Afinamos as tuas recomendações com base nestas escolhas.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($categories as $cat)
                        <label class="font-normal flex items-center gap-1.5 tag cursor-pointer"><input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="w-auto" @checked(in_array($cat->id, old('categories', $favoriteCategoryIds)))> {{ $cat->name }}</label>
                    @endforeach
                </div>

                @if ($tags->isNotEmpty())
                    <label class="mt-5">Tags favoritas</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <label class="font-normal flex items-center gap-1.5 tag cursor-pointer"><input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="w-auto" @checked(in_array($tag->id, old('tags', $favoriteTagIds)))> #{{ $tag->name }}</label>
                        @endforeach
                    </div>
                @endif

                <hr class="border-[var(--color-border)] my-6">

                <h2 class="font-display text-lg font-semibold">Preferências</h2>
                <label class="font-normal flex items-center gap-2 mt-3"><input type="checkbox" name="marketing_emails" value="1" class="w-auto" @checked(old('marketing_emails', $user->marketing_emails))> Receber o resumo semanal e novidades por email</label>
                <div class="flex items-center justify-between mt-3 text-sm">
                    <span>Aparência</span>
                    <span class="u-label">Escuro · mais temas em breve</span>
                </div>

                <button type="submit" class="btn-primary mt-6">Guardar perfil</button>
            </form>

            <form method="POST" action="{{ route('my.profile.password') }}" class="vcard p-6">
                @csrf @method('PUT')
                <h2 class="font-display text-lg font-semibold">Palavra-passe</h2>
                <label for="current_password">Palavra-passe atual</label>
                <input id="current_password" name="current_password" type="password" required>
                @error('current_password')<div class="err">{{ $message }}</div>@enderror
                <div class="grid grid-cols-2 gap-3">
                    <div><label for="password">Nova palavra-passe</label><input id="password" name="password" type="password" required>@error('password')<div class="err">{{ $message }}</div>@enderror</div>
                    <div><label for="password_confirmation">Confirmar</label><input id="password_confirmation" name="password_confirmation" type="password" required></div>
                </div>
                <button type="submit" class="btn-primary mt-5">Alterar palavra-passe</button>
            </form>
        </div>

        {{-- Right: hub widgets --}}
        <aside class="flex flex-col gap-5">
            <div class="vcard p-5">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-lg font-semibold">Promotores que segues</h2>
                    <a href="{{ route('promoters.index') }}" class="text-sm text-[var(--color-primary)]">ver todos</a>
                </div>
                @forelse ($followedPromoters as $promoter)
                    <a href="{{ route('promoters.show', $promoter) }}" class="flex items-center gap-3 mt-3 group">
                        <span class="w-9 h-9 rounded-full bg-[var(--color-surface-2)] grid place-items-center text-sm font-medium overflow-hidden shrink-0">
                            @if ($promoter->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($promoter->logo_path) }}" class="w-full h-full object-cover" alt="">@else{{ strtoupper(mb_substr($promoter->name, 0, 1)) }}@endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate group-hover:text-[var(--color-primary)] transition">{{ $promoter->name }}</span>
                            <span class="u-label">{{ $promoter->events_count }} {{ \Illuminate\Support\Str::plural('evento', $promoter->events_count) }}</span>
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-[var(--color-muted)] mt-3">Ainda não segues ninguém. <a href="{{ route('promoters.index') }}" class="text-[var(--color-primary)]">Descobre promotores</a>.</p>
                @endforelse
            </div>

            <div class="vcard p-5">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-lg font-semibold">Eventos favoritos</h2>
                    <a href="{{ route('my.favorites') }}" class="text-sm text-[var(--color-primary)]">ver todos</a>
                </div>
                @forelse ($favoriteEvents as $event)
                    @php $occ = $event->occurrences->first(); @endphp
                    <a href="{{ route('events.show', $event) }}" class="block mt-3 group">
                        <span class="u-label">{{ $occ?->starts_at->translatedFormat('D, d M · H:i') }}</span>
                        <span class="block font-medium truncate group-hover:text-[var(--color-primary)] transition">{{ $event->title }}</span>
                        @if ($occ?->venue)<span class="block text-sm text-[var(--color-muted)] truncate">{{ $occ->venue->name }}</span>@endif
                    </a>
                @empty
                    <p class="text-sm text-[var(--color-muted)] mt-3">Sem favoritos ainda. Toca no marcador de qualquer evento para guardar.</p>
                @endforelse
            </div>

            <div class="vcard p-5">
                <h2 class="font-display text-lg font-semibold">Fala connosco</h2>
                <p class="text-sm text-[var(--color-muted)] mt-2">Dúvidas, sugestões ou queres divulgar eventos?</p>
                <a href="mailto:ola@vagacultural.pt" class="block mt-2 text-[var(--color-primary)]">ola@vagacultural.pt</a>
                <a href="{{ route('promoter.apply') }}" class="btn-ghost text-sm mt-3 inline-block">Divulgar eventos</a>
            </div>
        </aside>
    </div>
@endsection
