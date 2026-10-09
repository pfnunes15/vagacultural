@extends('layouts.app')
@section('title', ($venue->exists ? 'Editar' : 'Novo') . ' local — Admin VAGA')
@section('content')
    <h1>{{ $venue->exists ? 'Editar local' : 'Novo local' }}</h1>
    <form method="POST" action="{{ $venue->exists ? route('admin.venues.update', $venue) : route('admin.venues.store') }}" style="max-width:560px">
        @csrf
        @if ($venue->exists) @method('PUT') @endif

        <label for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $venue->name) }}" required>
        @error('name')<div class="err">{{ $message }}</div>@enderror

        <label for="description">Descrição</label>
        <textarea id="description" name="description" rows="3" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('description', $venue->description) }}</textarea>

        <label for="address">Morada</label>
        <input id="address" name="address" value="{{ old('address', $venue->address) }}">
        <div style="display:flex;gap:10px">
            <div style="flex:2"><label for="municipality">Concelho</label><input id="municipality" name="municipality" value="{{ old('municipality', $venue->municipality) }}"></div>
            <div style="flex:1"><label for="postal_code">Código postal</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $venue->postal_code) }}"></div>
        </div>
        <div style="display:flex;gap:10px">
            <div style="flex:1"><label for="latitude">Latitude</label><input id="latitude" name="latitude" value="{{ old('latitude', $venue->latitude) }}"></div>
            <div style="flex:1"><label for="longitude">Longitude</label><input id="longitude" name="longitude" value="{{ old('longitude', $venue->longitude) }}"></div>
        </div>
        @error('latitude')<div class="err">{{ $message }}</div>@enderror
        <div style="display:flex;gap:10px">
            <div style="flex:1"><label for="website">Website</label><input id="website" name="website" type="url" value="{{ old('website', $venue->website) }}"></div>
            <div style="flex:1"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $venue->email) }}"></div>
            <div style="flex:1"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $venue->phone) }}"></div>
        </div>

        <label style="font-weight:400;display:flex;gap:8px;align-items:center;margin-top:12px">
            <input type="checkbox" name="is_active" value="1" style="width:auto" @checked(old('is_active', $venue->is_active))> Ativo
        </label>
        <p style="margin-top:16px"><button type="submit">Guardar</button> <a href="{{ route('admin.venues.index') }}" class="muted">Cancelar</a></p>
    </form>
@endsection
