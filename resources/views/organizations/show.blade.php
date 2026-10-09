@extends('layouts.app')
@section('title', $organization->name . ' — VAGA')
@section('content')
    <p><a href="{{ route('organizations.index') }}" class="muted">← Organizações</a></p>
    <h1>{{ $organization->name }}</h1>
    <div class="muted">{{ $organization->promoters_count }} promotor(es)</div>
    @if ($organization->description)<p>{{ $organization->description }}</p>@endif
    @if ($organization->website)<p class="muted"><a href="{{ $organization->website }}" target="_blank" rel="noopener">{{ $organization->website }}</a></p>@endif

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
                    <div class="muted">por {{ $event->promoter->name }}</div>
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $events->links() }}</div>
    @endif
@endsection
