<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VAGA — Agenda Cultural da Madeira')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <header class="sticky top-0 z-40 border-b border-[var(--color-border)]"
            style="background:color-mix(in oklab, var(--color-background) 85%, transparent); backdrop-filter:saturate(1.1) blur(10px);">
        <div class="mx-auto max-w-6xl px-4 md:px-8 h-16 flex items-center gap-5">
            <a href="{{ route('events.index') }}" class="font-display text-2xl font-bold tracking-tight">vaga</a>

            <nav class="hidden md:flex items-center gap-1 text-sm ml-2">
                <a class="px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)]" href="{{ route('events.index') }}">Eventos</a>
                <a class="px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-[var(--color-muted)] hover:text-[var(--color-foreground)]" href="{{ route('events.calendar') }}">Calendário</a>
                <a class="px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-[var(--color-muted)] hover:text-[var(--color-foreground)]" href="{{ route('promoters.index') }}">Promotores</a>
                <a class="px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-[var(--color-muted)] hover:text-[var(--color-foreground)]" href="{{ route('organizations.index') }}">Organizações</a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <form method="GET" action="{{ route('events.index') }}" class="hidden lg:block">
                    <input name="q" value="{{ request('q') }}" placeholder="Procurar eventos…"
                           class="w-56 px-3.5 py-2 rounded-xl bg-[var(--color-surface)] border border-[var(--color-border)] text-sm placeholder:text-[var(--color-muted)] focus:outline-none">
                </form>

                <select onchange="location.href='?lang='+this.value" aria-label="Idioma"
                        class="text-sm bg-[var(--color-surface)] border border-[var(--color-border)] rounded-lg pl-3 pr-7 py-2 cursor-pointer">
                    @foreach (config('locales.supported') as $code => $label)
                        <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ strtoupper($code) }}</option>
                    @endforeach
                </select>

                @auth
                    @php $u = auth()->user(); @endphp
                    @if ($u->isPromoter() || $u->isOrganization() || $u->isAdmin())
                        <a class="hidden sm:inline text-sm text-[var(--color-muted)] hover:text-[var(--color-foreground)] px-2" href="{{ route('dashboard.events.index') }}">Painel</a>
                    @endif
                    @if ($u->isAdmin())
                        <a class="hidden sm:inline text-sm text-[var(--color-muted)] hover:text-[var(--color-foreground)] px-2" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                    <div class="relative group">
                        <button class="w-9 h-9 rounded-full bg-[var(--color-surface-2)] grid place-items-center font-medium" aria-label="Conta">
                            {{ strtoupper(mb_substr($u->first_name ?? $u->name, 0, 1)) }}
                        </button>
                        <div class="absolute right-0 mt-2 w-48 vcard p-1.5 hidden group-hover:block">
                            <a class="block px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm" href="{{ route('my.recommendations') }}">Para ti</a>
                            <a class="block px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm" href="{{ route('my.favorites') }}">Guardados</a>
                            <a class="block px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm" href="{{ route('my.agenda') }}">A minha agenda</a>
                            <a class="block px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm" href="{{ route('my.tickets') }}">Bilhetes</a>
                            <a class="block px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm" href="{{ route('my.profile') }}">Perfil</a>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-[var(--color-border)] mt-1 pt-1">@csrf
                                <button class="block w-full text-left px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)] text-sm text-[var(--color-muted)]">Sair</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="text-sm px-3 py-2 rounded-lg hover:bg-[var(--color-surface-2)]" href="{{ route('login') }}">Entrar</a>
                    <a class="btn-primary text-sm" href="{{ route('register') }}">Criar conta</a>
                @endauth
            </div>
        </div>
    </header>

    @if (session()->has('impersonator_id'))
        <div class="bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-center text-sm py-2">
            A ver como <strong>{{ auth()->user()->name }}</strong>.
            <form method="POST" action="{{ route('impersonate.stop') }}" class="inline ml-2">@csrf<button class="underline">Voltar à minha conta</button></form>
        </div>
    @endif

    <main class="mx-auto max-w-6xl px-4 md:px-8 py-8">
        @if (session('status'))
            <div class="vcard border-[var(--color-primary)] px-4 py-3 mb-6 text-sm">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-[var(--color-border)] mt-10">
        <div class="mx-auto max-w-6xl px-4 md:px-8 py-10 flex flex-col sm:flex-row justify-between gap-4 text-sm text-[var(--color-muted)]">
            <div>
                <div class="font-display text-xl font-bold text-[var(--color-foreground)]">vaga</div>
                <p class="mt-1 max-w-xs">A agenda cultural da Madeira e do Porto Santo.</p>
            </div>
            <div class="flex gap-8">
                <nav class="flex flex-col gap-1.5">
                    <span class="u-label">Descobrir</span>
                    <a class="hover:text-[var(--color-foreground)]" href="{{ route('events.index') }}">Eventos</a>
                    <a class="hover:text-[var(--color-foreground)]" href="{{ route('events.calendar') }}">Calendário</a>
                    <a class="hover:text-[var(--color-foreground)]" href="{{ route('promoters.index') }}">Promotores</a>
                </nav>
                <nav class="flex flex-col gap-1.5">
                    <span class="u-label">VAGA</span>
                    <a class="hover:text-[var(--color-foreground)]" href="{{ route('promoter.apply') }}">Divulgar eventos</a>
                </nav>
            </div>
        </div>
        <div class="border-t border-[var(--color-border)] py-4 text-center text-xs text-[var(--color-muted)]">
            © {{ date('Y') }} VAGA · Madeira &amp; Porto Santo
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
