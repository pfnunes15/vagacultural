@extends('layouts.app')
@section('title', $event->title . ' — VAGA')
@section('content')
    <p><a href="{{ route('events.index') }}" class="muted">← Voltar aos eventos</a></p>
    @foreach ($event->categories as $cat)<span class="tag">{{ $cat->name }}</span>@endforeach
    @if ($event->min_age)<span class="tag" style="background:#fde8e6;color:#c0392b">{{ $event->min_age->label() }}</span>@endif
    <h1 style="margin:8px 0">{{ $event->title }}</h1>
    @if ($event->summary)<p style="font-size:18px">{{ $event->summary }}</p>@endif

    @if ($event->cover_image_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($event->cover_image_path) }}" alt="Capa de {{ $event->title }}" style="max-width:360px;border-radius:12px">
    @endif

    <div class="card" style="margin:16px 0">
        <strong>Sessões</strong>
        <ul>
            @forelse ($event->occurrences as $occ)
                <li>
                    @if ($occ->is_all_day && $occ->ends_at)
                        {{ $occ->starts_at->translatedFormat('d \d\e F') }} – {{ $occ->ends_at->translatedFormat('d \d\e F') }} (contínuo)
                    @else
                        {{ $occ->starts_at->translatedFormat('l, d \d\e F \à\s H:i') }}
                    @endif
                    — {{ $occ->locationLabel() }}
                    @if ($occ->is_online && $occ->online_url) (<a href="{{ $occ->online_url }}" target="_blank" rel="noopener">link</a>)@endif
                    @if ($occ->status->value !== 'scheduled') <span class="tag">{{ $occ->status->label() }}</span>@endif
                </li>
            @empty
                <li class="muted">Sem sessões agendadas.</li>
            @endforelse
        </ul>

        @if ($event->is_free)
            <p><strong>Entrada livre</strong></p>
        @elseif ($event->ticketTiers->isNotEmpty())
            <p><strong>Bilhetes</strong></p>
            <ul>
                @foreach ($event->ticketTiers as $tier)
                    <li>{{ $tier->name }}@if ($tier->min_age !== null || $tier->max_age !== null) ({{ $tier->min_age ?? 0 }}@if ($tier->max_age)–{{ $tier->max_age }}@else+@endif anos)@endif — {{ $tier->isFree() ? 'grátis' : '€' . number_format((float) $tier->price, 2, ',', ' ') }}</li>
                @endforeach
            </ul>
        @endif
        @if ($event->ticket_url)<p><a class="btn" href="{{ $event->ticket_url }}" target="_blank" rel="noopener">Comprar bilhetes</a></p>@endif
    </div>

    @if ($event->tags->isNotEmpty())
        <p class="muted">Tags: @foreach ($event->tags as $tag)<span class="tag">{{ $tag->name }}</span> @endforeach</p>
    @endif

    @if ($event->description)<div>{!! nl2br(e($event->description)) !!}</div>@endif

    <p class="muted" style="margin-top:24px">
        Promotor: {{ $event->promoter->name }}@if ($org = $event->organization()) · Organização: {{ $org->name }}@endif
    </p>
@endsection
