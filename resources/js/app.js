// VAGA — front-end behaviour. Talks to the first-party API via the session
// cookie (Sanctum stateful); no visual polish here, just functional wiring.

const csrf = () => document.querySelector('meta[name=csrf-token]')?.content ?? '';

async function apiPost(url) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        credentials: 'same-origin',
    });
    return r.ok ? r.json() : null;
}

// --- Toggles (favorite / agenda / follow) via event delegation ---
// Buttons declare: data-toggle="favorite|agenda|follow" data-slug="…" aria-pressed
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-toggle]');
    if (!btn) return;
    e.preventDefault();

    const slug = btn.dataset.slug;
    const map = {
        favorite: [`/api/v1/events/${slug}/favorite`, 'favorited'],
        agenda: [`/api/v1/events/${slug}/agenda`, 'in_agenda'],
        follow: [`/api/v1/promoters/${slug}/follow`, 'following'],
    };
    const entry = map[btn.dataset.toggle];
    if (!entry) return;

    btn.disabled = true;
    const data = await apiPost(entry[0]);
    btn.disabled = false;
    if (!data) return;

    const on = data[entry[1]];
    btn.setAttribute('aria-pressed', on);

    // Icon fill (bookmark/heart)
    const icon = btn.querySelector('svg');
    if (icon) icon.setAttribute('fill', on ? 'currentColor' : 'none');

    // Text label, if present
    const lbl = btn.querySelector('.lbl');
    if (lbl && btn.dataset.onLabel) lbl.textContent = on ? btn.dataset.onLabel : btn.dataset.offLabel;

    // Follower counter, if present
    const counter = document.getElementById(btn.dataset.counter || '');
    if (counter) counter.textContent = Math.max(0, (+counter.textContent || 0) + (on ? 1 : -1));
});

// --- Search as-you-type (suggestions dropdown) ---
function initSearch() {
    const input = document.querySelector('[data-search-input]');
    const box = document.querySelector('[data-search-results]');
    if (!input || !box) return;

    let timer;
    const close = () => { box.hidden = true; box.innerHTML = ''; };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) return close();
        timer = setTimeout(async () => {
            const r = await fetch(`/api/v1/events?q=${encodeURIComponent(q)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!r.ok) return close();
            const { data } = await r.json();
            if (!data || data.length === 0) {
                box.innerHTML = '<div class="px-4 py-3 text-sm" style="color:var(--color-muted)">Sem resultados</div>';
                box.hidden = false;
                return;
            }
            box.innerHTML = data.slice(0, 6).map((ev) => {
                const occ = ev.occurrences?.[0];
                const when = occ ? new Date(occ.starts_at).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short' }) : '';
                return `<a href="/event/${ev.slug}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-[var(--color-surface-2)]">
                    <span class="font-medium flex-1 truncate">${ev.title}</span>
                    <span class="text-xs" style="color:var(--color-muted)">${when}</span>
                </a>`;
            }).join('');
            box.hidden = false;
        }, 220);
    });

    document.addEventListener('click', (e) => {
        if (!box.contains(e.target) && e.target !== input) close();
    });
    input.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

// --- Dynamic category filter (swaps the events grid, no reload) ---
function initCategoryFilters() {
    const bar = document.querySelector('[data-cat-filters]');
    const grid = document.querySelector('[data-events-grid]');
    if (!bar || !grid) return;

    bar.addEventListener('click', async (e) => {
        const chip = e.target.closest('[data-cat]');
        if (!chip) return;
        bar.querySelectorAll('[data-cat]').forEach((c) => c.setAttribute('aria-pressed', 'false'));
        chip.setAttribute('aria-pressed', 'true');

        const slug = chip.dataset.cat;
        grid.style.opacity = '0.5';
        const url = slug ? `/events-grid?category=${encodeURIComponent(slug)}` : '/events-grid';
        const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        grid.style.opacity = '';
        if (r.ok) {
            grid.innerHTML = await r.text();
            const newUrl = slug ? `/category/${slug}` : '/events';
            history.replaceState({}, '', newUrl);
        }
    });
}

// --- Load more events (append next page, no reload) ---
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-load-more]');
    if (!btn) return;
    e.preventDefault();
    const grid = document.querySelector('[data-grid-items]');
    if (!grid) return;

    const params = new URLSearchParams({ page: btn.dataset.next });
    if (btn.dataset.category) params.set('category', btn.dataset.category);

    btn.disabled = true;
    btn.textContent = 'A carregar…';
    const r = await fetch(`/events-grid?${params}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    if (!r.ok) { btn.disabled = false; btn.textContent = 'Ver mais eventos'; return; }

    const html = await r.text();
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    tmp.querySelectorAll('[data-grid-items] > article').forEach((card) => grid.appendChild(card));

    const nextBtn = tmp.querySelector('[data-load-more]');
    const wrap = btn.parentElement;
    if (nextBtn) { btn.dataset.next = nextBtn.dataset.next; btn.disabled = false; btn.textContent = 'Ver mais eventos'; }
    else wrap.remove();
});

document.addEventListener('DOMContentLoaded', () => { initSearch(); initCategoryFilters(); });
