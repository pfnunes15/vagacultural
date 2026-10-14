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

            @if (!empty($trending))
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="u-label mr-1">Pesquisas em alta</span>
                    @foreach ($trending as $term)
                        <a href="{{ route('events.index', ['q' => $term]) }}"
                           class="pill-cat text-sm">{{ $term }}</a>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <div class="mb-6">
            <h1 class="font-display text-3xl font-bold">{{ $heading ?? 'Resultados' }}</h1>
            @if ($search)<p class="text-[var(--color-muted)] mt-1">Resultados para “{{ $search }}” · <a href="{{ route('events.index') }}" class="text-[var(--color-primary)]">limpar</a></p>@endif
            @if ($heading)<a href="{{ route('events.index') }}" class="text-sm text-[var(--color-primary)]">ver todos os eventos</a>@endif
        </div>
    @endunless

    @foreach ($sections ?? [] as $section)
        @include('partials.event-row', ['title' => $section['title'], 'events' => $section['events']])
    @endforeach

    @unless ($heading || $search)
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="font-display text-2xl font-semibold">Próximos eventos</h2>
        </div>
        @if (($categories ?? collect())->isNotEmpty())
            <div class="flex gap-2 overflow-x-auto pb-2 mb-5" data-cat-filters>
                <button class="pill-cat shrink-0" data-cat="" aria-pressed="true">Todas</button>
                @foreach ($categories as $cat)
                    <button class="pill-cat shrink-0" data-cat="{{ $cat->slug }}" aria-pressed="false">{{ $cat->name }}</button>
                @endforeach
            </div>
        @endif
    @endunless

    <div data-events-grid>
        @include('partials.events-grid')
    </div>
@endsection
