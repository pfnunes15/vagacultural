@extends('layouts.app')
@section('title', $event->title . ' — VAGA')
@section('content')
    @php
        $next = $event->occurrences->first();
        $isFav = auth()->check() && auth()->user()->favorites()->where('favoritable_type', $event->getMorphClass())->where('favoritable_id', $event->id)->exists();
        $inAgenda = auth()->check() && auth()->user()->agendaItems()->where('event_id', $event->id)->exists();
    @endphp

    <div class="grid lg:grid-cols-[1.4fr_1fr] gap-8 items-start">
        <div>
            {{-- Hero image --}}
            <div class="relative rounded-[var(--radius-card)] overflow-hidden aspect-[16/10]"
                 style="background:linear-gradient(135deg, color-mix(in oklab, var(--color-primary) 35%, var(--color-surface)), var(--color-surface));">
                @if ($event->coverImageUrl())
                    <img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">
                @endif
                <a href="{{ route('events.index') }}" class="absolute top-4 left-4 w-9 h-9 rounded-full bg-black/35 backdrop-blur grid place-items-center text-white" aria-label="Voltar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                @auth
                    <button data-toggle="favorite" data-slug="{{ $event->slug }}" aria-pressed="{{ $isFav ? 'true' : 'false' }}"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/35 backdrop-blur grid place-items-center text-white" aria-label="Guardar">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="{{ $isFav ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg>
                    </button>
                @endauth
            </div>

            <div class="mt-5 flex items-center gap-2 flex-wrap">
                @foreach ($event->categories as $cat)
                    <a href="{{ route('events.category', $cat) }}" class="pill-cat">{{ $cat->name }}</a>
                @endforeach
                @if ($event->min_age)<span class="pill-cat" style="background:color-mix(in oklab, oklch(65% .2 25) 20%, transparent); color:oklch(78% .15 25)">{{ $event->min_age->label() }}</span>@endif
            </div>
            <h1 class="font-display text-3xl md:text-4xl font-bold mt-3">{{ $event->title }}</h1>
            @if ($event->summary)<p class="text-[var(--color-muted)] text-lg mt-2">{{ $event->summary }}</p>@endif

            {{-- Stat tiles --}}
            @if ($next)
                <div class="grid grid-cols-3 gap-3 mt-6">
                    <div class="stat-tile">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--color-primary)" stroke-width="2" class="mx-auto mb-1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <div class="font-medium">{{ $next->starts_at->translatedFormat('D, d M') }}</div>
                        <div class="u-label mt-0.5">Data</div>
                    </div>
                    <div class="stat-tile">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--color-primary)" stroke-width="2" class="mx-auto mb-1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <div class="font-medium">{{ $next->starts_at->format('H:i') }}</div>
                        <div class="u-label mt-0.5">Início</div>
                    </div>
                    <div class="stat-tile">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--color-primary)" stroke-width="2" class="mx-auto mb-1.5"><path d="M3 9a2 2 0 0 1 0 6v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2a2 2 0 0 1 0-6V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/></svg>
                        <div class="font-medium">{{ $event->is_free ? 'Grátis' : ($event->price_from ? '€' . number_format((float) $event->price_from, 0) : '—') }}</div>
                        <div class="u-label mt-0.5">Bilhete</div>
                    </div>
                </div>
            @endif

            @if ($event->description)
                <h2 class="font-display text-xl font-semibold mt-8 mb-1">Sobre</h2>
                <div class="text-[var(--color-muted)] leading-relaxed">{!! nl2br(e($event->description)) !!}</div>
            @endif

            @if ($event->tags->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($event->tags as $tag)<a href="{{ route('events.tag', $tag) }}" class="tag">#{{ $tag->name }}</a>@endforeach
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-24 flex flex-col gap-4">
            {{-- Sessions + registration --}}
            <div class="vcard p-4">
                <h2 class="font-display text-lg font-semibold mb-3">Sessões</h2>
                @forelse ($event->occurrences as $occ)
                    <div class="@if(! $loop->first) border-t border-[var(--color-border)] pt-3 mt-3 @endif">
                        <div class="font-medium">
                            @if ($occ->is_all_day && $occ->ends_at)
                                {{ $occ->starts_at->translatedFormat('d \d\e F') }} – {{ $occ->ends_at->translatedFormat('d \d\e F') }}
                            @else
                                {{ $occ->starts_at->translatedFormat('l, d \d\e F · H:i') }}
                            @endif
                        </div>
                        <div class="text-sm text-[var(--color-muted)] flex items-center gap-1 mt-0.5">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            {{ $occ->locationLabel() }}@if ($occ->is_online && $occ->online_url) · <a href="{{ $occ->online_url }}" target="_blank" rel="noopener">link</a>@endif
                        </div>

                        @if ($occ->ticketTypes->isNotEmpty())
                            @auth
                                <form method="POST" action="{{ route('my.occurrences.register', $occ) }}" class="mt-3 space-y-2">
                                    @csrf
                                    @foreach ($occ->ticketTypes->where('is_active', true) as $type)
                                        <div class="flex items-center gap-2">
                                            <label class="flex-1 text-sm font-normal m-0">{{ $type->name }} — <span class="text-[var(--color-muted)]">{{ $type->isFree() ? 'grátis' : '€' . number_format((float) $type->price, 2, ',', ' ') }} · {{ $type->remaining() }} disp.</span></label>
                                            <input type="number" name="quantities[{{ $type->id }}]" min="0" max="{{ min(10, $type->remaining()) }}" value="0" class="w-16 text-center" @disabled($type->remaining() < 1)>
                                        </div>
                                    @endforeach
                                    <button class="btn-primary w-full mt-1">{{ $event->is_free ? 'Inscrever-me' : 'Comprar bilhete' }}</button>
                                </form>
                                @error('tickets')<div class="err">{{ $message }}</div>@enderror
                            @else
                                <a href="{{ route('login') }}" class="btn-primary w-full mt-3">Entra para te inscreveres</a>
                            @endauth
                        @endif
                    </div>
                @empty
                    <p class="text-[var(--color-muted)]">Sem sessões agendadas.</p>
                @endforelse
            </div>

            @auth
                <button data-toggle="agenda" data-slug="{{ $event->slug }}" aria-pressed="{{ $inAgenda ? 'true' : 'false' }}" data-on-label="✓ Na minha agenda" data-off-label="+ Adicionar à agenda" class="btn-ghost w-full justify-center">
                    <span class="lbl">{{ $inAgenda ? '✓ Na minha agenda' : '+ Adicionar à agenda' }}</span>
                </button>
            @endauth

            {{-- Promoter --}}
            <div class="vcard p-4 flex items-center gap-3">
                <div class="flex-1">
                    <div class="u-label">Promotor</div>
                    <a href="{{ route('promoters.show', $event->promoter) }}" class="font-medium">{{ $event->promoter->name }}</a>
                    @if ($org = $event->organization())<div class="text-sm text-[var(--color-muted)]">{{ $org->name }}</div>@endif
                </div>
                @auth
                    @php $following = auth()->user()->follows()->where('followable_type', $event->promoter->getMorphClass())->where('followable_id', $event->promoter->id)->exists(); @endphp
                    <button data-toggle="follow" data-slug="{{ $event->promoter->slug }}" aria-pressed="{{ $following ? 'true' : 'false' }}" data-on-label="A seguir ✓" data-off-label="Seguir" class="btn-ghost text-sm">
                        <span class="lbl">{{ $following ? 'A seguir ✓' : 'Seguir' }}</span>
                    </button>
                @endauth
            </div>
        </aside>
    </div>

@endsection
