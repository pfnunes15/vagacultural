@extends('layouts.app')
@section('title', 'Editar evento — VAGA')
@section('content')
    <div style="display:flex;align-items:center;gap:12px">
        <h1 style="margin-right:auto">Editar evento</h1>
        <span class="tag">{{ $event->status->label() }}</span>
    </div>
    <form method="POST" action="{{ route('painel.eventos.update', $event) }}" enctype="multipart/form-data" style="max-width:720px">
        @csrf
        @method('PUT')
        @include('promoter.events._form', ['event' => $event])
        <p style="margin-top:16px;display:flex;gap:10px;align-items:center">
            <button type="submit">Guardar alterações</button>
        </p>
    </form>

    <form method="POST" action="{{ route('painel.eventos.destroy', $event) }}" style="max-width:720px;margin-top:8px"
          onsubmit="return confirm('Remover este evento? Esta ação pode ser revertida por um administrador.')">
        @csrf
        @method('DELETE')
        <button class="btn-ghost" style="color:#c0392b;border-color:#c0392b">Remover evento</button>
    </form>
@endsection
