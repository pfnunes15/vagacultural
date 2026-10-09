@extends('layouts.app')
@section('title', $event->title . ' — VAGA')
@section('content')
    <p><a href="{{ route('events.index') }}" class="muted">← Voltar aos eventos</a></p>
    @foreach ($event->categories as $cat)<span class="tag">{{ $cat->name }}</span>@endforeach
    <h1 style="margin:8px 0">{{ $event->title }}</h1>
    @if ($event->summary)<p style="font-size:18px">{{ $event->summary }}</p>@endif

    <div class="card" style="margin:16px 0">
        <strong>Sessões</strong>
        <ul>
            @forelse ($event->occurrences as $occ)
                <li>
                    {{ $occ->starts_at->translatedFormat('l, d \d\e F \à\s H:i') }}
                    @if ($occ->venue) — {{ $occ->venue->name }}@if ($occ->venue->municipality) ({{ $occ->venue->municipality }})@endif @endif
                    @if ($occ->status->value !== 'scheduled') <span class="tag">{{ $occ->status->label() }}</span>@endif
                </li>
            @empty
                <li class="muted">Sem sessões agendadas.</li>
            @endforelse
        </ul>
        <p>@if ($event->is_free)<strong>Entrada livre</strong>@elseif ($event->price_from)A partir de €{{ number_format((float) $event->price_from, 2, ',', ' ') }}@endif</p>
        @if ($event->ticket_url)<p><a class="btn" href="{{ $event->ticket_url }}" target="_blank" rel="noopener">Bilhetes</a></p>@endif
    </div>

    @if ($event->description)<div>{!! nl2br(e($event->description)) !!}</div>@endif

    <p class="muted" style="margin-top:24px">
        Promotor: {{ $event->promoter->name }}@if ($org = $event->organization()) · Organização: {{ $org->name }}@endif
    </p>
@endsection
