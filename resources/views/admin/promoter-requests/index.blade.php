@extends('layouts.app')
@section('title', 'Pedidos de promotor — Admin VAGA')
@section('content')
    <h1>Candidaturas a promotor</h1>
    @if ($requests->isEmpty())
        <div class="card">Não há candidaturas pendentes. 🎉</div>
    @else
        @foreach ($requests as $req)
            <div class="card" style="margin-bottom:12px">
                <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
                    <div style="margin-right:auto">
                        <strong>{{ $req->proposed_name }}</strong>
                        <div class="muted">Candidato: {{ $req->user?->name }} · {{ $req->email }}@if ($req->phone) · {{ $req->phone }}@endif</div>
                        @if ($req->website)<div class="muted">{{ $req->website }}</div>@endif
                        @if ($req->message)<p>{{ $req->message }}</p>@endif
                    </div>
                    <form method="POST" action="{{ route('admin.promoters.requests.approve', $req) }}">@csrf<button class="btn">Aprovar</button></form>
                    <form method="POST" action="{{ route('admin.promoters.requests.reject', $req) }}" style="display:flex;gap:6px">
                        @csrf
                        <input name="notes" placeholder="Motivo (opcional)" style="width:180px">
                        <button class="btn-ghost">Recusar</button>
                    </form>
                </div>
            </div>
        @endforeach
        <div>{{ $requests->links() }}</div>
    @endif
@endsection
