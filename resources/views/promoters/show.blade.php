@extends('layouts.app')
@section('title', $promoter->name . ' — VAGA')
@section('content')
    <p><a href="{{ route('promoters.index') }}" class="muted">← Promotores</a></p>
    <h1>{{ $promoter->name }}</h1>
    @if ($promoter->is_verified)<span class="tag">verificado</span>@endif
    @if ($promoter->organization)<span class="muted">· {{ $promoter->organization->name }}</span>@endif
    @if ($promoter->description)<p>{{ $promoter->description }}</p>@endif
    @if ($promoter->website)<p class="muted"><a href="{{ $promoter->website }}" target="_blank" rel="noopener">{{ $promoter->website }}</a></p>@endif

    <h2 style="margin-top:24px">Próximos eventos</h2>
    @if ($events->isEmpty())
        <div class="card">Sem eventos próximos.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                @php $next = $event->occurrences->first(); @endphp
                <article class="card">
                    <h3 style="margin:0 0 4px"><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                    @if ($next)<div class="muted">{{ $next->starts_at->translatedFormat('D, d M · H:i') }} · {{ $next->locationLabel() }}</div>@endif
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $events->links() }}</div>
    @endif
@endsection
