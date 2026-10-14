@extends('layouts.app')
@section('title', 'Editar evento — VAGA')
@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <h1 class="font-display text-3xl font-bold mr-auto">Editar evento</h1>
            <span class="pill-cat">{{ $event->status->label() }}</span>
        </div>

        @if ($event->occurrences->isNotEmpty())
            <div class="vcard p-5 mb-5">
                <h2 class="font-display font-semibold mb-2">Bilhetes por sessão</h2>
                <ul class="space-y-1">
                    @foreach ($event->occurrences as $occ)
                        <li class="text-sm">{{ $occ->starts_at->translatedFormat('d \d\e F · H:i') }} —
                            <a href="{{ route('dashboard.occurrences.tickets', $occ) }}">gerir bilhetes</a> ·
                            <a href="{{ route('dashboard.occurrences.checkin', $occ) }}">check-in</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.events.update', $event) }}" enctype="multipart/form-data" class="vcard p-6">
            @csrf @method('PUT')
            @include('promoter.events._form', ['event' => $event])
            <button type="submit" class="btn-primary mt-6">Guardar alterações</button>
        </form>

        <form method="POST" action="{{ route('dashboard.events.destroy', $event) }}" class="mt-4" onsubmit="return confirm('Remover este evento?')">
            @csrf @method('DELETE')
            <button class="btn-ghost text-sm" style="color:oklch(72% .17 25)">Remover evento</button>
        </form>
    </div>
@endsection
