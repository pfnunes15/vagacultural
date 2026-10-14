@extends('layouts.app')
@section('title', 'Os meus eventos — VAGA')
@section('content')
    <div class="flex items-center gap-3 mb-6">
        <h1 class="font-display text-3xl font-bold mr-auto">Os meus eventos</h1>
        <a class="btn-ghost text-sm" href="{{ route('dashboard.profile') }}">Perfil público</a>
        @if ($canCreate)<a class="btn-primary text-sm" href="{{ route('dashboard.events.create') }}">Novo evento</a>@endif
    </div>

    @if ($needsPromoter)
        <div class="notice">A tua organização precisa de estar associada a pelo menos um promotor para criar eventos.</div>
    @endif

    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Ainda não tens eventos. @if ($canCreate)<a href="{{ route('dashboard.events.create') }}">Cria o primeiro</a>.@endif</div>
    @else
        <div class="vcard overflow-hidden">
            <table>
                <thead><tr><th>Evento</th><th>Promotor</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td class="font-medium">{{ $event->title }}</td>
                        <td class="text-[var(--color-muted)]">{{ $event->promoter->name }}</td>
                        <td><span class="pill-cat">{{ $event->status->label() }}</span></td>
                        <td class="text-right"><a class="text-sm" href="{{ route('dashboard.events.edit', $event) }}">Editar</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $events->links() }}</div>
    @endif
@endsection
