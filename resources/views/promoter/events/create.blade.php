@extends('layouts.app')
@section('title', 'Novo evento — VAGA')
@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="font-display text-3xl font-bold mb-1">Novo evento</h1>
        <p class="text-[var(--color-muted)] mb-6">Preenche os detalhes do evento.</p>
        <form method="POST" action="{{ route('dashboard.events.store') }}" enctype="multipart/form-data" class="vcard p-6">
            @csrf
            @include('promoter.events._form', ['event' => null])
            <button type="submit" class="btn-primary mt-6">Submeter evento</button>
        </form>
    </div>
@endsection
