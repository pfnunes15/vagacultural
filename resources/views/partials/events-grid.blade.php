@if ($events->isEmpty())
    <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem eventos a mostrar.</div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($events as $event)
            @include('partials.event-card')
        @endforeach
    </div>
    @if (method_exists($events, 'links'))
        <div class="mt-8">{{ $events->links() }}</div>
    @endif
@endif
