@extends('layouts.app')
@section('title', 'Os meus bilhetes — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">Os meus bilhetes</h1>
    @if ($orders->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Ainda não tens bilhetes. Explora a <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($orders as $order)
                <div class="vcard p-5">
                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="font-display font-semibold">{{ $order->occurrence->event->title }}</h3>
                        <span class="u-label">Ref. {{ $order->reference }}</span>
                    </div>
                    <div class="text-sm text-[var(--color-muted)] mt-0.5">{{ $order->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }} — {{ $order->occurrence->locationLabel() }}</div>
                    <div class="grid sm:grid-cols-2 gap-3 mt-4">
                        @foreach ($order->tickets as $ticket)
                            <div class="flex items-center gap-3 bg-[var(--color-surface-2)] rounded-xl p-3">
                                <img src="{{ route('my.tickets.qr', $ticket) }}" alt="QR" width="64" height="64" class="rounded-lg bg-white p-1 flex-none">
                                <div class="min-w-0">
                                    <div class="font-medium">{{ $ticket->type->name }}</div>
                                    <div class="text-xs font-mono text-[var(--color-muted)] truncate">{{ $ticket->code }}</div>
                                    @if ($ticket->status->value === 'used')<span class="tag">usado</span>@elseif ($ticket->status->value === 'cancelled')<span class="tag">cancelado</span>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
