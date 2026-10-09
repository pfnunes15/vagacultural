@extends('layouts.app')
@section('title', 'Admin — VAGA')
@section('content')
    <h1>Central de administração</h1>
    <div class="grid" style="margin:16px 0">
        @foreach ($stats as $label => $value)
            <div class="card"><div class="muted" style="text-transform:capitalize">{{ str_replace('_',' ',$label) }}</div><div style="font-size:28px;font-weight:800">{{ $value }}</div></div>
        @endforeach
    </div>

    <div class="card" style="margin-bottom:16px">
        <strong>Utilizadores por papel</strong>
        <ul>@foreach ($rolesBreakdown as $role => $total)<li>{{ $role }}: {{ $total }}</li>@endforeach</ul>
    </div>

    <h2>Módulos</h2>
    <ul>
        <li><a href="{{ route('admin.users.index') }}">Gerir utilizadores</a> — papéis, confiança, reenvio de e-mail, impersonação</li>
        <li><a href="{{ route('admin.eventos.pending') }}">Eventos pendentes</a> — aprovar / recusar</li>
        <li><a href="{{ route('admin.promoters.requests') }}">Candidaturas a promotor</a> — aprovar / recusar</li>
        <li><a href="{{ route('admin.system.status') }}">Estado do sistema / APIs</a></li>
    </ul>
@endsection
