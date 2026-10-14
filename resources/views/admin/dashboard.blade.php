@extends('layouts.app')
@section('title', 'Admin — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">Central de administração</h1>

    <div class="grid sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach ($stats as $label => $value)
            <div class="stat-tile">
                <div class="font-display text-2xl font-bold">{{ $value }}</div>
                <div class="u-label mt-0.5 capitalize">{{ str_replace('_', ' ', $label) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 gap-5">
        <div class="vcard p-5">
            <h2 class="font-display font-semibold mb-2">Utilizadores por papel</h2>
            <ul class="space-y-1 text-sm">
                @foreach ($rolesBreakdown as $role => $total)
                    <li class="flex justify-between"><span class="text-[var(--color-muted)] capitalize">{{ $role }}</span><span class="font-medium">{{ $total }}</span></li>
                @endforeach
            </ul>
        </div>

        <div class="vcard p-5">
            <h2 class="font-display font-semibold mb-2">Módulos</h2>
            <div class="flex flex-col divide-y divide-[var(--color-border)]">
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.users.index') }}">Gerir utilizadores</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.events.pending') }}">Eventos pendentes</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.promoters.requests') }}">Candidaturas a promotor</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.categories.index') }}">Categorias</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.venues.index') }}">Locais</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.organizations.index') }}">Organizações</a>
                <a class="py-2 hover:text-[var(--color-primary)]" href="{{ route('admin.system.status') }}">Estado do sistema / APIs</a>
            </div>
        </div>
    </div>
@endsection
