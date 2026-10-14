@extends('layouts.app')
@section('title', 'Mapa — VAGA')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
    #vaga-map { height: 72vh; min-height: 480px; border-radius: var(--radius-card); border: 1px solid var(--color-border); }
    .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: var(--color-surface); color: var(--color-foreground); border: 1px solid var(--color-border); }
    .leaflet-popup-content a { color: var(--color-primary); }
    .leaflet-container { background: var(--color-surface); }
    /* Darken keyless OSM raster tiles to match the dark theme (markers live in the SVG overlay pane, unaffected). */
    .leaflet-tile-pane { filter: invert(1) hue-rotate(180deg) brightness(0.92) contrast(0.9) saturate(0.7); }
</style>
@endpush

@section('content')
    <div class="flex items-baseline justify-between mb-5">
        <div>
            <h1 class="font-display text-3xl font-bold">Mapa</h1>
            <p class="text-[var(--color-muted)] mt-1">Eventos pela Madeira e Porto Santo.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn-ghost text-sm">Ver em lista</a>
    </div>
    <div id="vaga-map"></div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(async function () {
    const map = L.map('vaga-map', { scrollWheelZoom: false }).setView([32.70, -16.90], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19,
    }).addTo(map);

    const r = await fetch('{{ route('events.map.points') }}', { headers: { Accept: 'application/json' } });
    if (!r.ok) return;
    const { data } = await r.json();
    const coral = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim();
    const bounds = [];

    data.forEach((p) => {
        const m = L.circleMarker([p.lat, p.lng], { radius: 8, color: coral, fillColor: coral, fillOpacity: 0.85, weight: 2 }).addTo(map);
        m.bindPopup(
            `<strong>${p.title}</strong><br>` +
            (p.category ? `<span style="opacity:.7">${p.category}</span><br>` : '') +
            `${p.when} · ${p.venue}<br>` +
            `<a href="/event/${p.slug}">Ver evento →</a>`
        );
        bounds.push([p.lat, p.lng]);
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 });
})();
</script>
@endpush
