@extends('layouts.app')
@section('title', ($heading ?? 'Eventos') . ' — VAGA')
@section('content')

    @unless ($heading || $search)
        {{-- Hero --}}
        <section class="mb-10">
            <p class="u-label mb-3">
                @auth Boa, {{ auth()->user()->first_name }} · @endauth Madeira &amp; Porto Santo
            </p>
            <h1 class="font-display text-4xl md:text-6xl font-bold leading-[1.02] max-w-3xl">
                Descobre o que <span class="italic text-[var(--color-primary)]">acontece hoje.</span>
            </h1>
            <form method="GET" action="{{ route('events.index') }}" class="mt-6 flex gap-2 max-w-xl">
                <div class="relative flex-1">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--color-muted)]" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                    <input name="q" value="{{ $search }}" placeholder="Procurar eventos, locais, promotores…" data-search-input autocomplete="off"
                           class="w-full pl-11 pr-4 py-3 rounded-xl bg-[var(--color-surface)] border border-[var(--color-border)] placeholder:text-[var(--color-muted)] focus:outline-none">
                    <div data-search-results hidden class="absolute left-0 right-0 top-full mt-2 vcard overflow-hidden z-20"></div>
                </div>
                <button class="btn-primary">Procurar</button>
            </form>
        </section>
    @else
        <div class="mb-6">
            <h1 class="font-display text-3xl font-bold">{{ $heading ?? 'Resultados' }}</h1>
            @if ($search)<p class="text-[var(--color-muted)] mt-1">Resultados para “{{ $search }}” · <a href="{{ route('events.index') }}" class="text-[var(--color-primary)]">limpar</a></p>@endif
            @if ($heading)<a href="{{ route('events.index') }}" class="text-sm text-[var(--color-primary)]">ver todos os eventos</a>@endif
        </div>
    @endunless

    <div class="flex items-baseline justify-between mb-4">
        <h2 class="font-display text-2xl font-semibold">{{ $heading ?? ($search ? 'Resultados' : 'Próximos eventos') }}</h2>
    </div>

    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos a mostrar.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($events as $event)
                @include('partials.event-card')
            @endforeach
        </div>
        <div class="mt-8">{{ $events->links() }}</div>
    @endif
@endsection
