@extends('layouts.app')
@section('title', $organization->name . ' — VAGA')
@section('content')
    <p><a href="{{ route('organizations.index') }}" class="muted">← Organizações</a></p>
    <div style="display:flex;gap:14px;align-items:center">
        @if ($organization->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($organization->logo_path) }}" alt="{{ $organization->name }}" style="height:64px;width:64px;object-fit:cover;border-radius:10px;border:1px solid #e4dccb">@endif
        <div>
            <h1 style="margin:0">{{ $organization->name }}</h1>
            <div class="muted">{{ $organization->promoters_count }} promotor(es) · {{ $organization->followers()->count() }} seguidor(es)</div>
        </div>
    </div>
    @auth
        <form method="POST" action="{{ route('my.follow.organization', $organization) }}" style="margin-top:10px">@csrf
            <button class="btn-ghost">{{ auth()->user()->follows()->where('followable_type', $organization->getMorphClass())->where('followable_id', $organization->id)->exists() ? 'A seguir ✓' : 'Seguir' }}</button>
        </form>
    @endauth
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
