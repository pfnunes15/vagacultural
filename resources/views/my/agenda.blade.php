@extends('layouts.app')
@section('title', 'A minha agenda — VAGA')
@section('content')
    <h1>A minha agenda</h1>
    @if ($items->isEmpty())
        <div class="card">A tua agenda está vazia. Marca eventos a partir da <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        @foreach ($items as $item)
            <div class="card" style="margin-bottom:10px;display:flex;gap:12px;align-items:center">
                <div style="margin-right:auto">
                    <strong><a href="{{ route('events.show', $item->event) }}">{{ $item->event->title }}</a></strong>
                    @if ($item->occurrence)<div class="muted">{{ $item->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }} — {{ $item->occurrence->locationLabel() }}</div>@endif
                </div>
                <form method="POST" action="{{ route('my.agenda.toggle', $item->event) }}">@csrf<button class="btn-ghost">Remover</button></form>
            </div>
        @endforeach
    @endif
@endsection
