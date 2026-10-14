@extends('layouts.app')
@section('title', $promoter->name . ' — VAGA')
@section('content')
    <a href="{{ route('promoters.index') }}" class="text-sm text-[var(--color-muted)] hover:text-[var(--color-foreground)]">← Promotores</a>

    <div class="vcard p-6 mt-4 flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[var(--color-surface-2)] grid place-items-center font-display font-bold text-2xl flex-none overflow-hidden">
            @if ($promoter->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($promoter->logo_path) }}" class="w-full h-full object-cover" alt="">@else{{ strtoupper(mb_substr($promoter->name,0,1)) }}@endif
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="font-display text-2xl font-bold">{{ $promoter->name }}</h1>
            <div class="text-sm text-[var(--color-muted)] mt-0.5">
                @if ($promoter->is_verified)✓ verificado · @endif
                <span id="followerCount">{{ $promoter->followers()->count() }}</span> seguidor(es)
                @if ($promoter->organization) · {{ $promoter->organization->name }}@endif
            </div>
            @if ($promoter->description)<p class="mt-2 text-[var(--color-muted)]">{{ $promoter->description }}</p>@endif
        </div>
        @auth
            @php $following = auth()->user()->follows()->where('followable_type', $promoter->getMorphClass())->where('followable_id', $promoter->id)->exists(); @endphp
            <button id="followBtn" data-slug="{{ $promoter->slug }}" aria-pressed="{{ $following ? 'true' : 'false' }}" class="{{ $following ? 'btn-ghost' : 'btn-primary' }}">
                <span class="lbl">{{ $following ? 'A seguir ✓' : 'Seguir' }}</span>
            </button>
        @endauth
    </div>

    <h2 class="font-display text-xl font-semibold mt-8 mb-4">Próximos eventos</h2>
    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos próximos.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($events as $event)@include('partials.event-card')@endforeach
        </div>
        <div class="mt-8">{{ $events->links() }}</div>
    @endif

    @auth
        @push('scripts')
        <script>
        (function(){
            const b = document.getElementById('followBtn'); if(!b) return;
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            b.addEventListener('click', async () => {
                const r = await fetch(`/api/v1/promoters/${b.dataset.slug}/follow`, {method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},credentials:'same-origin'});
                if(!r.ok) return; const d = await r.json();
                b.setAttribute('aria-pressed', d.following);
                b.className = d.following ? 'btn-ghost' : 'btn-primary';
                b.querySelector('.lbl').textContent = d.following ? 'A seguir ✓' : 'Seguir';
                const c = document.getElementById('followerCount'); if(c) c.textContent = Math.max(0, (+c.textContent) + (d.following?1:-1));
            });
        })();
        </script>
        @endpush
    @endauth
@endsection
