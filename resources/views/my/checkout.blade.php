@extends('layouts.app')
@section('title', 'Pagamento — VAGA')
@section('content')
    <h1>Finalizar compra</h1>
    <div class="card" style="max-width:480px">
        <strong>{{ $order->occurrence->event->title }}</strong>
        <div class="muted">{{ $order->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }}</div>
        <div class="muted mono">Ref. {{ $order->reference }}</div>
        <ul>
            @foreach ($order->tickets->groupBy('ticket_type_id') as $group)
                <li>{{ $group->count() }}× {{ $group->first()->type->name }} — €{{ number_format((float) $group->first()->type->price, 2, ',', ' ') }}</li>
            @endforeach
        </ul>
        <p style="font-size:18px"><strong>Total: €{{ number_format((float) $order->total, 2, ',', ' ') }}</strong></p>
        <p class="muted">Reserva válida até {{ $order->reserved_until?->format('H:i') }}.</p>

        @if ($simulated)
            <div class="notice">Modo de demonstração: o pagamento é simulado (Stripe será ligado depois).</div>
            <div style="display:flex;gap:8px;margin-top:10px">
                <form method="POST" action="{{ route('my.checkout.pay', $order) }}">@csrf<button class="btn">Pagar (simulado)</button></form>
                <form method="POST" action="{{ route('my.checkout.cancel', $order) }}">@csrf<button class="btn-ghost">Cancelar</button></form>
            </div>
        @else
            <p class="muted">O pagamento será processado de forma segura pelo Stripe.</p>
        @endif
    </div>
@endsection
