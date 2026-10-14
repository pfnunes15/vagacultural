@php $next = $event->occurrences->first(); @endphp
<article class="vcard overflow-hidden group">
    <a href="{{ route('events.show', $event) }}" class="block">
        <div class="aspect-[16/10] relative overflow-hidden"
             style="background:linear-gradient(135deg, color-mix(in oklab, var(--color-primary) 30%, var(--color-surface)), var(--color-surface));">
            @if ($event->cover_image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($event->cover_image_path) }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">
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
