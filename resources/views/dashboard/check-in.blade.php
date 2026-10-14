@extends('layouts.app')
@section('title', 'Check-in — VAGA')
@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="font-display text-2xl font-bold">Check-in</h1>
        <p class="text-[var(--color-muted)]">{{ $occurrence->event->title }} · {{ $occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }}</p>

        @if ($c = session('checkin'))
            @php $ok = $c['status'] === 'ok'; @endphp
            <div class="vcard p-4 mt-4 font-semibold text-lg" style="background: {{ $ok ? 'color-mix(in oklab, oklch(65% .2 150) 18%, var(--color-surface))' : 'color-mix(in oklab, oklch(65% .2 25) 18%, var(--color-surface))' }}; border-color: {{ $ok ? 'oklch(65% .2 150)' : 'oklch(65% .2 25)' }}">{{ $c['message'] }}</div>
        @endif

        <form method="POST" action="{{ route('dashboard.occurrences.checkin.scan', $occurrence) }}" class="mt-4 flex gap-2">
            @csrf
            <input name="code" placeholder="Código do bilhete (QR)" autofocus required class="font-mono flex-1">
            <button class="btn-primary">Validar</button>
        </form>
        <p class="text-sm text-[var(--color-muted)] mt-2">Lê o QR do bilhete (ou escreve o código) para validar a entrada.</p>
    </div>
@endsection
