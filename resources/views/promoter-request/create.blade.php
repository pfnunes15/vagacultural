@extends('layouts.app')
@section('title', 'Tornar-me promotor — VAGA')
@section('content')
    <h1>Tornar-me promotor</h1>
    <p class="muted">Promotores podem publicar eventos na agenda. Submete a tua candidatura — a equipa VAGA revê e aprova.</p>

    @if ($pending)
        <div class="notice">Já tens uma candidatura pendente. Entraremos em contacto após a revisão.</div>
    @else
        <form method="POST" action="{{ route('promoter.apply') }}" style="max-width:520px">
            @csrf
            <label for="proposed_name">Nome do promotor / entidade</label>
            <input id="proposed_name" name="proposed_name" value="{{ old('proposed_name') }}" required>
            @error('proposed_name')<div class="err">{{ $message }}</div>@enderror

            <label for="email">Email de contacto</label>
            <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}">
            @error('email')<div class="err">{{ $message }}</div>@enderror

            <label for="phone">Telefone</label>
            <input id="phone" name="phone" value="{{ old('phone') }}">

            <label for="website">Website</label>
            <input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://">
            @error('website')<div class="err">{{ $message }}</div>@enderror

            <label for="message">Mensagem (que tipo de eventos organizas?)</label>
            <textarea id="message" name="message" rows="4" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('message') }}</textarea>

            <p style="margin-top:16px"><button type="submit">Enviar candidatura</button></p>
        </form>
    @endif
@endsection
