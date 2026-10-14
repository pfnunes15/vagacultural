@if ($events->isEmpty())
    <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos a mostrar.</div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5" data-grid-items>
        @foreach ($events as $event)
            @include('partials.event-card')
        @endforeach
    </div>
    @if (method_exists($events, 'hasMorePages') && $events->hasMorePages())
        <div class="mt-8 text-center">
            <button class="btn-ghost" data-load-more
                    data-next="{{ $events->currentPage() + 1 }}"
                    data-category="{{ $category ?? '' }}">
                Ver mais eventos
            </button>
        </div>
    @endif
@endif
