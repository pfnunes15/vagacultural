@extends('layouts.app')
@section('title', 'Calendário — VAGA')
@section('content')
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold">Calendário</h1>
        <p class="text-[var(--color-muted)] mt-1">Próximos 30 dias na Madeira e Porto Santo.</p>
    </div>

    @forelse ($days as $day => $occurrences)
        <h2 class="font-display text-lg font-semibold mt-8 mb-3 flex items-center gap-3">
            {{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l, d \d\e F') }}
            <span class="h-px flex-1 bg-[var(--color-border)]"></span>
        </h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($occurrences as $occ)
                <a href="{{ route('events.show', $occ->event) }}" class="vcard p-4 group">
                    <div class="u-label">{{ $occ->starts_at->format('H:i') }}</div>
                    <h3 class="font-display font-semibold mt-1 group-hover:text-[var(--color-primary)] transition">{{ $occ->event->title }}</h3>
                    <div class="text-sm text-[var(--color-muted)] mt-1">{{ $occ->locationLabel() }}</div>
                    <div class="mt-2 flex gap-1.5 flex-wrap">
                        @foreach ($occ->event->categories as $cat)<span class="pill-cat">{{ $cat->name }}</span>@endforeach
                    </div>
                </a>
            @endforeach
        </div>
    @empty
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos agendados nos próximos 30 dias.</div>
    @endforelse
@endsection
