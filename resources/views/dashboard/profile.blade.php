@extends('layouts.app')
@section('title', 'Perfil público — VAGA')
@section('content')
    <h1>Perfil de {{ $type }}</h1>
    <p class="muted">Estes dados aparecem no teu perfil público.</p>

    <form method="POST" action="{{ route('dashboard.profile.update') }}" enctype="multipart/form-data" style="max-width:560px">
        @csrf @method('PUT')

        @if ($entity->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($entity->logo_path) }}" alt="Logo" style="height:80px;border-radius:8px;border:1px solid #e4dccb">
        @endif
        <label for="logo">Logótipo</label>
        <input id="logo" name="logo" type="file" accept="image/*">
        @error('logo')<div class="err">{{ $message }}</div>@enderror

        <label for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $entity->name) }}" required>
        @error('name')<div class="err">{{ $message }}</div>@enderror

        <label for="description">Descrição</label>
        <textarea id="description" name="description" rows="4" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('description', $entity->description) }}</textarea>

        <div style="display:flex;gap:10px">
            <div style="flex:1"><label for="website">Website</label><input id="website" name="website" type="url" value="{{ old('website', $entity->website) }}"></div>
            <div style="flex:1"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $entity->email) }}"></div>
            <div style="flex:1"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $entity->phone) }}"></div>
        </div>

        <p style="margin-top:16px"><button type="submit">Guardar perfil</button></p>
    </form>
@endsection
