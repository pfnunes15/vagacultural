@extends('layouts.app')
@section('title', 'Para ti — VAGA')
@section('content')
    <h1>Recomendados para ti</h1>
    <p class="muted">Sugestões com base nas tuas categorias favoritas, em quem segues e no que guardaste.</p>

    @if ($events->isEmpty())
        <div class="card">Ainda não há recomendações. Explora a <a href="{{ route('events.index') }}">agenda</a>, segue promotores e guarda eventos.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                @php $next = $event->occurrences->first(); @endphp
                <article class="card">
                    @if ($event->recommendation_reason)<span class="tag" style="background:#e8f5e9;color:#0e5b4f">{{ $event->recommendation_reason }}</span>@endif
                    @foreach ($event->categories as $cat)<a href="{{ route('events.category', $cat) }}" class="tag" style="text-decoration:none">{{ $cat->name }}</a>@endforeach
                    <h3 style="margin:8px 0 4px"><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                    @if ($next)<div class="muted">{{ $next->starts_at->translatedFormat('D, d M · H:i') }} · {{ $next->locationLabel() }}</div>@endif
                    <div class="muted">por {{ $event->promoter->name }}</div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
