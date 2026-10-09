@extends('layouts.app')
@section('title', 'Novo evento — VAGA')
@section('content')
    <h1>Novo evento</h1>
    <p class="muted">Formulário provisório — os campos finais serão ajustados. O que importa aqui é o fluxo de submissão.</p>

    <form method="POST" action="{{ route('painel.eventos.store') }}" style="max-width:640px">
        @csrf
        <label for="promoter_id">Publicar como</label>
        <select id="promoter_id" name="promoter_id" required style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
            @foreach ($promoters as $promoter)
                <option value="{{ $promoter->id }}">{{ $promoter->name }}@if ($promoter->auto_publish) (publicação automática)@endif</option>
            @endforeach
        </select>
        @error('promoter_id')<div class="err">{{ $message }}</div>@enderror

        <label for="title">Título</label>
        <input id="title" name="title" value="{{ old('title') }}" required>
        @error('title')<div class="err">{{ $message }}</div>@enderror

        <label for="summary">Resumo</label>
        <input id="summary" name="summary" value="{{ old('summary') }}">

        <label for="description">Descrição</label>
        <textarea id="description" name="description" rows="5" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('description') }}</textarea>

        <label style="font-weight:400;display:flex;gap:8px;align-items:center;margin-top:12px">
            <input type="checkbox" name="is_free" value="1" style="width:auto" @checked(old('is_free')) onchange="document.getElementById('price').disabled=this.checked"> Entrada livre
        </label>
        <label for="price">Preço desde (€)</label>
        <input id="price" name="price_from" type="number" step="0.01" min="0" value="{{ old('price_from') }}">
        @error('price_from')<div class="err">{{ $message }}</div>@enderror

        <label>Categorias</label>
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach ($categories as $cat)
                <label style="font-weight:400;display:flex;gap:6px;align-items:center">
                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" style="width:auto"> {{ $cat->name }}
                </label>
            @endforeach
        </div>

        <fieldset style="margin-top:16px;border:1px solid #e4dccb;border-radius:8px;padding:12px">
            <legend>Sessão</legend>
            <label for="starts">Início</label>
            <input id="starts" name="occurrences[0][starts_at]" type="datetime-local" required>
            @error('occurrences.0.starts_at')<div class="err">{{ $message }}</div>@enderror
            <label for="ends">Fim (opcional)</label>
            <input id="ends" name="occurrences[0][ends_at]" type="datetime-local">
            <label for="venue">Local</label>
            <select id="venue" name="occurrences[0][venue_id]" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
                <option value="">— sem local —</option>
                @foreach ($venues as $venue)<option value="{{ $venue->id }}">{{ $venue->name }}</option>@endforeach
            </select>
        </fieldset>

        <p style="margin-top:16px"><button type="submit">Submeter evento</button></p>
    </form>
@endsection
