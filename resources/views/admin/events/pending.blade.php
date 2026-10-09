@extends('layouts.app')
@section('title', 'Eventos pendentes — VAGA')
@section('content')
    <h1>Eventos pendentes de aprovação</h1>
    @if ($events->isEmpty())
        <div class="card">Não há eventos pendentes. 🎉</div>
    @else
        @foreach ($events as $event)
            <div class="card" style="margin-bottom:12px;display:flex;gap:12px;align-items:center">
                <div style="margin-right:auto">
                    <strong>{{ $event->title }}</strong>
                    <div class="muted">{{ $event->promoter->name }}@if ($org = $event->organization()) · {{ $org->name }}@endif</div>
                </div>
                <form method="POST" action="{{ route('admin.eventos.approve', $event) }}">@csrf<button class="btn">Aprovar</button></form>
                <form method="POST" action="{{ route('admin.eventos.reject', $event) }}">@csrf<button class="btn-ghost">Rejeitar</button></form>
            </div>
        @endforeach
        <div style="margin-top:16px">{{ $events->links() }}</div>
    @endif
@endsection
