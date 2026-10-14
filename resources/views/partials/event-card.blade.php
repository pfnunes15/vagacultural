@php
    $next = $event->occurrences->first();
    $saved = in_array($event->id, $myFavoriteEventIds ?? []);
@endphp
<article class="vcard overflow-hidden group relative">
    @auth
        <button data-toggle="favorite" data-slug="{{ $event->slug }}" aria-pressed="{{ $saved ? 'true' : 'false' }}" aria-label="Guardar"
                class="absolute z-10 top-3 right-3 w-8 h-8 rounded-full bg-black/35 backdrop-blur grid place-items-center text-white">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="{{ $saved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg>
        </button>
    @endauth
    <a href="{{ route('events.show', $event) }}" class="block">
        <div class="aspect-[16/10] relative overflow-hidden"
             style="background:linear-gradient(135deg, color-mix(in oklab, var(--color-primary) 30%, var(--color-surface)), var(--color-surface));">
            @if ($event->coverImageUrl())
                <img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">
            @endif
            @if ($cat = $event->categories->first())
                <span class="pill-cat absolute top-3 left-3 backdrop-blur-sm">{{ $cat->name }}</span>
            @endif
        </div>
        <div class="p-4">
            @if ($next)
                <div class="u-label">{{ $next->starts_at->translatedFormat('D, d M · H:i') }}</div>
            @endif
            <h3 class="font-display text-lg font-semibold leading-tight mt-1 group-hover:text-[var(--color-primary)] transition">{{ $event->title }}</h3>
            @if ($next)
                <div class="text-sm text-[var(--color-muted)] mt-1 flex items-center gap-1">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--color-primary)]"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ $next->locationLabel() }}
                </div>
            @endif
        </div>
    </a>
</article>
