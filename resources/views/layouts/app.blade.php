<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VAGA — Agenda Cultural')</title>
    {{-- Marcação neutra/provisória: substituída pelo design final (Blade + Tailwind). --}}
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font: 16px/1.5 system-ui, sans-serif; color: #1a1a1a; background: #faf8f3; }
        a { color: #0e5b4f; }
        header, main, footer { max-width: 960px; margin: 0 auto; padding: 0 20px; }
        header { display: flex; gap: 16px; align-items: center; height: 60px; border-bottom: 1px solid #e4dccb; }
        header .brand { font-weight: 800; font-size: 20px; letter-spacing: -.02em; text-decoration: none; color: #0e5b4f; }
        header nav { display: flex; gap: 14px; margin-left: auto; align-items: center; }
        main { padding-top: 24px; padding-bottom: 48px; }
        .flash { background: #e8f5e9; border: 1px solid #0e5b4f; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; }
        .notice { background: #fff4e5; border: 1px solid #cf9427; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; }
        .card { border: 1px solid #e4dccb; border-radius: 10px; padding: 16px; background: #fff; }
        .grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
        .muted { color: #6b7772; font-size: 14px; }
        label { display: block; font-weight: 600; margin: 12px 0 4px; }
        input { width: 100%; padding: 9px 11px; border: 1px solid #cbccc9; border-radius: 8px; font: inherit; }
        button, .btn { display: inline-block; padding: 9px 16px; border: 0; border-radius: 8px; background: #ff5a36; color: #fff; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn-ghost { background: transparent; color: #0e5b4f; border: 1px solid #e4dccb; }
        .err { color: #c0392b; font-size: 14px; margin-top: 6px; }
        .tag { font-size: 12px; padding: 2px 8px; border-radius: 999px; background: #eef2f0; color: #0e5b4f; }
    </style>
</head>
<body>
    <header>
        <a href="{{ route('events.index') }}" class="brand">VAGA</a>
        <nav>
            <a href="{{ route('events.index') }}">Eventos</a>
            <a href="{{ route('events.calendar') }}">Calendário</a>
            @auth
                @php $u = auth()->user(); @endphp
                @if ($u->isPromoter() || $u->isOrganization() || $u->isAdmin())
                    <a href="{{ route('painel.eventos.index') }}">Painel</a>
                @endif
                @if (! $u->isPromoter() && ! $u->isOrganization() && ! $u->isAdmin())
                    <a href="{{ route('promoter.apply') }}">Tornar-me promotor</a>
                @endif
                @if ($u->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <span class="muted">{{ $u->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button class="btn-ghost">Sair</button></form>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}" class="btn">Registar</a>
            @endauth
        </nav>
    </header>
    @if (session()->has('impersonator_id'))
        <div style="background:#cf9427;color:#1a1a1a;text-align:center;padding:8px">
            A ver a plataforma como <strong>{{ auth()->user()->name }}</strong>.
            <form method="POST" action="{{ route('impersonate.stop') }}" style="display:inline;margin-left:8px">@csrf<button class="btn-ghost" style="background:#fff">Voltar à minha conta</button></form>
        </div>
    @endif
    <main>
        @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif
        @yield('content')
    </main>
    <footer class="muted" style="border-top:1px solid #e4dccb; padding-top:16px; padding-bottom:24px;">
        © {{ date('Y') }} VAGA · Agenda Cultural da Madeira e Porto Santo
    </footer>
</body>
</html>
