@extends('layouts.app')
@section('title', 'A minha agenda — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">A minha agenda</h1>
    @if ($items->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">A tua agenda está vazia. Marca eventos a partir da <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($items as $item)
                <div class="vcard p-4 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('events.show', $item->event) }}" class="font-display font-semibold">{{ $item->event->title }}</a>
                        @if ($item->occurrence)<div class="text-sm text-[var(--color-muted)] mt-0.5">{{ $item->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }} — {{ $item->occurrence->locationLabel() }}</div>@endif
                    </div>
                    <form method="POST" action="{{ route('my.agenda.toggle', $item->event) }}">@csrf<button class="btn-ghost text-sm">Remover</button></form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
