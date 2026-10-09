@extends('layouts.app')
@section('title', 'Calendário — VAGA')
@section('content')
    <h1>Calendário</h1>
    <p class="muted">Próximos 30 dias. Clica num evento para ver os detalhes (requer login).</p>

    @forelse ($days as $day => $occurrences)
        <h2 style="margin:24px 0 8px">{{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('l, d \d\e F') }}</h2>
        <div class="grid">
            @foreach ($occurrences as $occ)
                <article class="card">
                    <div class="muted">{{ $occ->starts_at->format('H:i') }}@if ($occ->venue) · {{ $occ->venue->name }}@endif</div>
                    <h3 style="margin:6px 0"><a href="{{ route('events.show', $occ->event) }}">{{ $occ->event->title }}</a></h3>
                    @foreach ($occ->event->categories as $cat)<span class="tag">{{ $cat->name }}</span>@endforeach
                </article>
            @endforeach
        </div>
    @empty
        <div class="card">Sem eventos agendados nos próximos 30 dias.</div>
    @endforelse
@endsection
