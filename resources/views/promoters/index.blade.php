@extends('layouts.app')
@section('title', 'Promotores — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">Promotores</h1>
    @if ($promoters->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Sem promotores.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($promoters as $promoter)
                <a href="{{ route('promoters.show', $promoter) }}" class="vcard p-5 flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-full bg-[var(--color-surface-2)] grid place-items-center font-display font-bold text-lg flex-none overflow-hidden">
                        @if ($promoter->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($promoter->logo_path) }}" class="w-full h-full object-cover" alt="">@else{{ strtoupper(mb_substr($promoter->name,0,1)) }}@endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-display font-semibold truncate group-hover:text-[var(--color-primary)] transition">{{ $promoter->name }}</h3>
                        @if ($promoter->is_verified)<span class="u-label">✓ verificado</span>@endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $promoters->links() }}</div>
    @endif
@endsection
