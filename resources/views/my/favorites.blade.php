@extends('layouts.app')
@section('title', 'Guardados — VAGA')
@section('content')
    <h1 class="font-display text-3xl font-bold mb-6">Guardados</h1>
    @if ($events->isEmpty())
        <div class="vcard p-8 text-center text-[var(--color-muted)]">Ainda não guardaste eventos. Explora a <a href="{{ route('events.index') }}">agenda</a>.</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">@foreach ($events as $event)@include('partials.event-card')@endforeach</div>
        <div class="mt-8">{{ $events->links() }}</div>
    @endif
@endsection
