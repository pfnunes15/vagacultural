@extends('layouts.app')
@section('title', $organization->name . ' — VAGA')
@section('content')
    <a href="{{ route('organizations.index') }}" class="text-sm text-[var(--color-muted)] hover:text-[var(--color-foreground)]">← Organizações</a>
    <div class="vcard p-6 mt-4 flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[var(--color-surface-2)] grid place-items-center font-display font-bold text-2xl flex-none overflow-hidden">
            @if ($organization->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($organization->logo_path) }}" class="w-full h-full object-cover" alt="">@else{{ strtoupper(mb_substr($organization->name,0,1)) }}@endif
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="font-display text-2xl font-bold">{{ $organization->name }}</h1>
            <div class="text-sm text-[var(--color-muted)] mt-0.5">{{ $organization->promoters_count }} promotor(es) · {{ $organization->followers()->count() }} seguidor(es)</div>
            @if ($organization->description)<p class="mt-2 text-[var(--color-muted)]">{{ $organization->description }}</p>@endif
        </div>
        @auth
            @php $following = auth()->user()->follows()->where('followable_type', $organization->getMorphClass())->where('followable_id', $organization->id)->exists(); @endphp
            <form method="POST" action="{{ route('my.follow.organization', $organization) }}">@csrf<button class="{{ $following ? 'btn-ghost' : 'btn-primary' }}">{{ $following ? 'A seguir ✓' : 'Seguir' }}</button></form>
        @endauth
    </div>

    <h2 class="font-display text-xl font-semibold mt-8 mb-4">Próximos eventos</h2>
    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos próximos.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">@foreach ($events as $event)@include('partials.event-card')@endforeach</div>
        <div class="mt-8">{{ $events->links() }}</div>
    @endif
@endsection
