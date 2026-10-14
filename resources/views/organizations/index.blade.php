@extends('layouts.app')
@section('title', 'Organizações — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">Organizações</h1>
    @if ($organizations->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem organizações.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($organizations as $organization)
                <a href="{{ route('organizations.show', $organization) }}" class="vcard p-5 flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-full bg-[var(--color-surface-2)] grid place-items-center font-display font-bold text-lg flex-none overflow-hidden">
                        @if ($organization->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($organization->logo_path) }}" class="w-full h-full object-cover" alt="">@else{{ strtoupper(mb_substr($organization->name,0,1)) }}@endif
                    </div>
                    <h3 class="font-display font-semibold truncate group-hover:text-[var(--color-primary)] transition">{{ $organization->name }}</h3>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $organizations->links() }}</div>
    @endif
@endsection
