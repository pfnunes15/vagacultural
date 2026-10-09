@extends('layouts.app')
@section('title', 'Os meus eventos — VAGA')
@section('content')
    <div style="display:flex;align-items:center;gap:12px">
        <h1 style="margin-right:auto">Os meus eventos</h1>
        @if ($canCreate)
            <a class="btn" href="{{ route('painel.eventos.create') }}">Novo evento</a>
        @endif
    </div>

    @if ($needsPromoter)
        <div class="notice">A tua organização precisa de estar associada a pelo menos um promotor para criar eventos.</div>
    @endif

    @if ($events->isEmpty())
        <div class="card">
            Ainda não tens eventos.
            @if ($canCreate)<a href="{{ route('painel.eventos.create') }}">Cria o primeiro</a>.@endif
        </div>
    @else
        <table style="width:100%;border-collapse:collapse;margin-top:12px">
            <thead><tr style="text-align:left;border-bottom:1px solid #e4dccb">
                <th style="padding:8px 6px">Evento</th><th>Promotor</th><th>Estado</th>
            </tr></thead>
            <tbody>
            @foreach ($events as $event)
                <tr style="border-bottom:1px solid #eee">
                    <td style="padding:8px 6px">{{ $event->title }}</td>
                    <td class="muted">{{ $event->promoter->name }}</td>
                    <td><span class="tag">{{ $event->status->label() }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px">{{ $events->links() }}</div>
    @endif
@endsection
