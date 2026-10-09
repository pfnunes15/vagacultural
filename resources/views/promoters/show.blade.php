@extends('layouts.app')
@section('title', $promoter->name . ' — VAGA')
@section('content')
    <p><a href="{{ route('promoters.index') }}" class="muted">← Promotores</a></p>
    <div style="display:flex;gap:14px;align-items:center">
        @if ($promoter->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($promoter->logo_path) }}" alt="{{ $promoter->name }}" style="height:64px;width:64px;object-fit:cover;border-radius:10px;border:1px solid #e4dccb">@endif
        <div>
            <h1 style="margin:0">{{ $promoter->name }}</h1>
            @if ($promoter->is_verified)<span class="tag">verificado</span>@endif
            <span class="muted">· {{ $promoter->followers()->count() }} seguidor(es)</span>
        </div>
    </div>
    @auth
        <form method="POST" action="{{ route('my.follow.promoter', $promoter) }}" style="margin-top:10px">@csrf
            <button class="btn-ghost">{{ auth()->user()->follows()->where('followable_type', $promoter->getMorphClass())->where('followable_id', $promoter->id)->exists() ? 'A seguir ✓' : 'Seguir' }}</button>
        </form>
    @endauth
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
