@extends('layouts.app')
@section('title', ($heading ?? 'Eventos') . ' — VAGA')
@section('content')
    <h1>{{ $heading ?? 'Eventos' }}</h1>
    <p class="muted">Agenda cultural da Madeira e Porto Santo. A lista e o calendário são públicos — entra para veres os detalhes.</p>

    <form method="GET" action="{{ route('events.index') }}" style="display:flex;gap:8px;margin:12px 0;max-width:480px">
        <input name="q" value="{{ $search }}" placeholder="Procurar evento, resumo ou promotor…">
        <button class="btn-ghost">Procurar</button>
    </form>

    @if ($heading)
        <p><span class="tag">{{ $heading }}</span> <a href="{{ route('events.index') }}" class="muted">ver todos</a></p>
    @elseif ($search)
        <p class="muted">Resultados para “{{ $search }}” — <a href="{{ route('events.index') }}">limpar</a></p>
    @endif

    @if ($events->isEmpty())
        <div class="card">Sem eventos a mostrar.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                @php $next = $event->occurrences->first(); @endphp
                <article class="card">
                    @foreach ($event->categories as $cat)
                        <a href="{{ route('events.category', $cat) }}" class="tag" style="text-decoration:none">{{ $cat->name }}</a>
                    @endforeach
                    <h3 style="margin:8px 0 4px"><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                    @if ($next)
                        <div class="muted">{{ $next->starts_at->translatedFormat('D, d M · H:i') }} · {{ $next->locationLabel() }}</div>
                    @endif
                    <p class="muted">{{ $event->summary }}</p>
                    <div class="muted">por <a href="{{ route('promoters.show', $event->promoter) }}">{{ $event->promoter->name }}</a></div>
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $events->links() }}</div>
    @endif
@endsection
