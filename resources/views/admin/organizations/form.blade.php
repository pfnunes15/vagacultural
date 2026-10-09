@extends('layouts.app')
@section('title', ($organization->exists ? 'Editar' : 'Nova') . ' organização — Admin VAGA')
@section('content')
    <h1>{{ $organization->exists ? 'Editar organização' : 'Nova organização' }}</h1>
    <form method="POST" action="{{ $organization->exists ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}" style="max-width:560px">
        @csrf
        @if ($organization->exists) @method('PUT') @endif

        <label for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $organization->name) }}" required>
        @error('name')<div class="err">{{ $message }}</div>@enderror

        <label for="user_id">Conta dona (gere a organização)</label>
        <select id="user_id" name="user_id" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
            <option value="">— sem conta associada —</option>
            @foreach ($users as $u)<option value="{{ $u->id }}" @selected(old('user_id', $organization->user_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach
        </select>
        <div class="muted">Ao associar uma conta, esta recebe o papel de organização.</div>

        <label for="description">Descrição</label>
        <textarea id="description" name="description" rows="3" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('description', $organization->description) }}</textarea>
        <div style="display:flex;gap:10px">
            <div style="flex:1"><label for="website">Website</label><input id="website" name="website" type="url" value="{{ old('website', $organization->website) }}"></div>
            <div style="flex:1"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $organization->email) }}"></div>
            <div style="flex:1"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $organization->phone) }}"></div>
        </div>
        <label style="font-weight:400;display:flex;gap:8px;align-items:center;margin-top:12px">
            <input type="checkbox" name="is_active" value="1" style="width:auto" @checked(old('is_active', $organization->is_active))> Ativa
        </label>
        <p style="margin-top:16px"><button type="submit">Guardar</button> <a href="{{ route('admin.organizations.index') }}" class="muted">Cancelar</a></p>
    </form>
@endsection
