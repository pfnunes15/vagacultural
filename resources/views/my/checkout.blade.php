@extends('layouts.app')
@section('title', 'Pagamento — VAGA')
@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="font-display text-2xl font-bold mb-4">Finalizar compra</h1>
        <div class="vcard p-6">
            <h2 class="font-display font-semibold">{{ $order->occurrence->event->title }}</h2>
            <div class="text-sm text-[var(--color-muted)]">{{ $order->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }}</div>
            <div class="u-label mt-1">Ref. {{ $order->reference }}</div>
            <div class="border-t border-[var(--color-border)] my-4"></div>
            @foreach ($order->tickets->groupBy('ticket_type_id') as $group)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $group->count() }}× {{ $group->first()->type->name }}</span>
                    <span>€{{ number_format((float) $group->first()->type->price, 2, ',', ' ') }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-display text-lg font-bold mt-3">
                <span>Total</span><span>€{{ number_format((float) $order->total, 2, ',', ' ') }}</span>
            </div>
            <p class="text-sm text-[var(--color-muted)] mt-1">Reserva válida até {{ $order->reserved_until?->format('H:i') }}.</p>

            @if ($simulated)
                <div class="notice mt-4">Modo de demonstração: o pagamento é simulado (Stripe será ligado depois).</div>
                <div class="flex gap-2 mt-4">
                    <form method="POST" action="{{ route('my.checkout.pay', $order) }}" class="flex-1">@csrf<button class="btn-primary w-full">Pagar (simulado)</button></form>
                    <form method="POST" action="{{ route('my.checkout.cancel', $order) }}">@csrf<button class="btn-ghost">Cancelar</button></form>
                </div>
            @else
                <p class="text-[var(--color-muted)] mt-4">O pagamento será processado de forma segura pelo Stripe.</p>
            @endif
        </div>
    </div>
@endsection
