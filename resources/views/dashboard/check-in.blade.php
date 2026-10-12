@extends('layouts.app')
@section('title', 'Check-in — VAGA')
@section('content')
    <h1>Check-in · {{ $occurrence->event->title }}</h1>
    <p class="muted">{{ $occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }}</p>

    @if ($c = session('checkin'))
        @php $bg = $c['status']==='ok' ? '#e8f5e9' : '#fde8e6'; $fg = $c['status']==='ok' ? '#0e5b4f' : '#c0392b'; @endphp
        <div class="card" style="background:{{ $bg }};color:{{ $fg }};font-weight:600;font-size:18px">{{ $c['message'] }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.occurrences.checkin.scan', $occurrence) }}" style="margin-top:12px;display:flex;gap:8px;max-width:420px">
        @csrf
        <input name="code" placeholder="Código do bilhete (QR)" autofocus required style="flex:1;font-family:monospace">
        <button class="btn">Validar</button>
    </form>
    <p class="muted" style="margin-top:8px">Lê o QR do bilhete (ou escreve o código) para validar a entrada.</p>
@endsection
