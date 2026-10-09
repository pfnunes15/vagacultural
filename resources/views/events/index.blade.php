@extends('layouts.app')
@section('title', 'Eventos — VAGA')
@section('content')
    <h1>Eventos</h1>
    <p class="muted">Agenda cultural da Madeira e Porto Santo. A lista e o calendário são públicos — entra para veres os detalhes.</p>

    @if ($category)
        <p><span class="tag">{{ $category }}</span> <a href="{{ route('events.index') }}" class="muted">limpar</a></p>
    @endif

    @if ($events->isEmpty())
        <div class="card">Sem eventos próximos de momento.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                @php $next = $event->occurrences->first(); @endphp
                <article class="card">
                    @foreach ($event->categories as $cat)
                        <span class="tag">{{ $cat->name }}</span>
                    @endforeach
                    <h3 style="margin:8px 0 4px"><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                    @if ($next)
                        <div class="muted">{{ $next->starts_at->translatedFormat('D, d M · H:i') }}@if ($next->venue) · {{ $next->venue->name }}@endif</div>
                    @endif
                    <p class="muted">{{ $event->summary }}</p>
                    <p>@if ($event->is_free)<strong>Entrada livre</strong>@elseif ($event->price_from)desde €{{ number_format((float) $event->price_from, 2, ',', ' ') }}@endif</p>
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $events->withQueryString()->links() }}</div>
    @endif
@endsection
