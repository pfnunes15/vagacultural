@extends('layouts.app')
@section('title', 'Bilhetes — VAGA')
@section('content')
    <p><a href="{{ route('dashboard.events.index') }}" class="muted">← Painel</a></p>
    <h1>Bilhetes · {{ $occurrence->event->title }}</h1>
    <p class="muted">{{ $occurrence->starts_at->translatedFormat('l, d \d\e F · H:i') }} — {{ $occurrence->locationLabel() }}</p>
    @error('ticket_type')<div class="notice">{{ $message }}</div>@enderror

    <h2 style="margin-top:20px">Tipos de bilhete</h2>
    @forelse ($occurrence->ticketTypes as $type)
        <div class="card" style="margin-bottom:10px">
            <form method="POST" action="{{ route('dashboard.occurrences.tickets.update', [$occurrence, $type]) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
                @csrf @method('PUT')
                <div><label>Nome</label><input name="name" value="{{ $type->name }}" style="width:160px"></div>
                <div><label>Preço (€)</label><input name="price" type="number" step="0.01" min="0" value="{{ $type->price }}" style="width:90px"></div>
                <div><label>Lotação</label><input name="capacity" type="number" min="1" value="{{ $type->capacity }}" style="width:90px"></div>
                <label style="font-weight:400;display:flex;gap:6px;align-items:center"><input type="checkbox" name="is_active" value="1" style="width:auto" @checked($type->is_active)> Ativo</label>
                <button class="btn-ghost">Guardar</button>
                <span class="muted mono">{{ $type->remaining() }}/{{ $type->capacity }} disponíveis</span>
            </form>
            @if (! $type->tickets()->where('status','!=','cancelled')->exists())
                <form method="POST" action="{{ route('dashboard.occurrences.tickets.destroy', [$occurrence, $type]) }}" style="margin-top:6px" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn-ghost" style="color:#c0392b;border:0;padding:0">Remover</button></form>
            @endif
        </div>
    @empty
        <div class="card">Ainda não há tipos de bilhete.</div>
    @endforelse

    <h3 style="margin-top:16px">Novo tipo</h3>
    <form method="POST" action="{{ route('dashboard.occurrences.tickets.store', $occurrence) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;max-width:620px">
        @csrf
        <div><label>Nome</label><input name="name" value="{{ old('name') }}" required style="width:160px"></div>
        <div><label>Preço (€)</label><input name="price" type="number" step="0.01" min="0" value="{{ old('price', 0) }}" required style="width:90px"></div>
        <div><label>Lotação</label><input name="capacity" type="number" min="1" value="{{ old('capacity') }}" required style="width:90px"></div>
        <button class="btn">Adicionar</button>
    </form>
    @error('capacity')<div class="err">{{ $message }}</div>@enderror

    <p style="margin-top:20px"><a class="btn-ghost" href="{{ route('dashboard.occurrences.checkin', $occurrence) }}">Abrir check-in →</a></p>
@endsection
