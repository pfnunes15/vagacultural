@extends('layouts.app')
@section('title', 'Os meus favoritos — VAGA')
@section('content')
    <h1>Os meus favoritos</h1>
    @if ($events->isEmpty())
        <div class="card">Ainda não guardaste eventos. Explora a <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        <div class="grid">
            @foreach ($events as $event)
                @php $next = $event->occurrences->first(); @endphp
                <article class="card">
                    @foreach ($event->categories as $cat)<span class="tag">{{ $cat->name }}</span>@endforeach
                    <h3 style="margin:8px 0 4px"><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                    @if ($next)<div class="muted">{{ $next->starts_at->translatedFormat('D, d M · H:i') }}@if ($next->venue) · {{ $next->venue->name }}@endif</div>@endif
                    <form method="POST" action="{{ route('my.favorites.toggle', $event) }}" style="margin-top:8px">@csrf<button class="btn-ghost">Remover</button></form>
                </article>
            @endforeach
        </div>
        <div style="margin-top:16px">{{ $events->links() }}</div>
    @endif
@endsection
