@extends('layouts.app')
@section('title', 'Para ti — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold">Para ti</h1>
    <p class="text-[var(--color-muted)] mt-1 mb-6">Com base nos teus gostos, em quem segues e no que guardaste.</p>
    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Ainda não há recomendações. Explora a <a href="{{ route('events.index') }}">agenda</a>, segue promotores e guarda eventos.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($events as $event)
                <div class="relative">
                    @if ($event->recommendation_reason)
                        <span class="absolute z-10 top-3 right-3 pill-cat" style="background:color-mix(in oklab, var(--color-primary) 20%, transparent); color:var(--color-primary)">{{ $event->recommendation_reason }}</span>
                    @endif
                    @include('partials.event-card')
                </div>
            @endforeach
        </div>
    @endif
@endsection
