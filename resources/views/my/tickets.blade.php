@extends('layouts.app')
@section('title', 'Os meus bilhetes — VAGA')
@section('content')
    <h1>Os meus bilhetes</h1>
    @if ($orders->isEmpty())
        <div class="card">Ainda não tens bilhetes. Explora a <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        @foreach ($orders as $order)
            <div class="card" style="margin-bottom:12px">
                <strong>{{ $order->occurrence->event->title }}</strong>
                <div class="muted">{{ $order->occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }} — {{ $order->occurrence->locationLabel() }}</div>
                <div class="muted mono">Ref. {{ $order->reference }}</div>
                <ul>
                    @foreach ($order->tickets as $ticket)
                        <li>
                            {{ $ticket->type->name }} — <span class="mono">{{ $ticket->code }}</span>
                            @if ($ticket->status->value === 'used')<span class="tag">usado</span>@elseif ($ticket->status->value === 'cancelled')<span class="tag">cancelado</span>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @endif
@endsection
