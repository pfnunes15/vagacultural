@extends('layouts.app')
@section('title', ($category->exists ? 'Editar' : 'Nova') . ' categoria — Admin VAGA')
@section('content')
    <h1>{{ $category->exists ? 'Editar categoria' : 'Nova categoria' }}</h1>
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" style="max-width:560px">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <label>Nome (por idioma)</label>
        @foreach ($locales as $code => $label)
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
                <span class="tag" style="min-width:34px;text-align:center">{{ strtoupper($code) }}</span>
                <input name="name[{{ $code }}]" value="{{ old('name.' . $code, $category->getTranslation('name', $code, false)) }}" @if($code === config('locales.default')) required @endif style="flex:1">
            </div>
        @endforeach
        @error('name.' . config('locales.default'))<div class="err">{{ $message }}</div>@enderror

        <label for="parent_id">Categoria-pai</label>
        <select id="parent_id" name="parent_id" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
            <option value="">— nenhuma —</option>
            @foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach
        </select>

        <div style="display:flex;gap:10px">
            <div style="flex:1"><label for="color">Cor</label><input id="color" name="color" type="text" value="{{ old('color', $category->color) }}" placeholder="#C8922A"></div>
            <div style="flex:1"><label for="icon">Ícone</label><input id="icon" name="icon" value="{{ old('icon', $category->icon) }}"></div>
            <div style="flex:1"><label for="position">Ordem</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $category->position ?? 0) }}"></div>
        </div>

        <label style="font-weight:400;display:flex;gap:8px;align-items:center;margin-top:12px">
            <input type="checkbox" name="is_active" value="1" style="width:auto" @checked(old('is_active', $category->is_active))> Ativa
        </label>

        <p style="margin-top:16px"><button type="submit">Guardar</button> <a href="{{ route('admin.categories.index') }}" class="muted">Cancelar</a></p>
    </form>
@endsection
