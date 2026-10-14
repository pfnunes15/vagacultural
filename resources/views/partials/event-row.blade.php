<section class="mt-8">
    <div class="flex items-baseline justify-between mb-3">
        <h2 class="font-display text-2xl font-semibold">{{ $title }}</h2>
    </div>
    <div class="flex gap-4 overflow-x-auto pb-3 -mx-1 px-1 snap-x">
        @foreach ($events as $event)
            @php $next = $event->occurrences->first(); @endphp
            <a href="{{ route('events.show', $event) }}" class="vcard overflow-hidden group shrink-0 w-72 snap-start">
                <div class="aspect-[16/10] relative overflow-hidden" style="background:linear-gradient(135deg, color-mix(in oklab, var(--color-primary) 30%, var(--color-surface)), var(--color-surface));">
                    @if ($event->coverImageUrl())<img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">@endif
                    @if ($cat = $event->categories->first())<span class="pill-cat absolute top-3 left-3 backdrop-blur-sm">{{ $cat->name }}</span>@endif
                </div>
                <div class="p-4">
                    @if ($next)<div class="u-label">{{ $next->starts_at->translatedFormat('D, d M · H:i') }}</div>@endif
                    <h3 class="font-display font-semibold leading-tight mt-1 group-hover:text-[var(--color-primary)] transition">{{ $event->title }}</h3>
                    @if ($next)<div class="text-sm text-[var(--color-muted)] mt-1">{{ $next->locationLabel() }}</div>@endif
                </div>
            </a>
        @endforeach
    </div>
</section>
