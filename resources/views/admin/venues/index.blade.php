@extends('layouts.app')
@section('title', 'Locais — Admin VAGA')
@section('content')
    <div style="display:flex;align-items:center;gap:12px">
        <h1 style="margin-right:auto">Locais</h1>
        <a class="btn" href="{{ route('admin.venues.create') }}">Novo local</a>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-top:12px">
        <thead><tr style="text-align:left;border-bottom:1px solid #e4dccb"><th style="padding:8px 6px">Nome</th><th>Concelho</th><th>Ativo</th><th></th></tr></thead>
        <tbody>
        @foreach ($venues as $venue)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:8px 6px">{{ $venue->name }}</td>
                <td class="muted">{{ $venue->municipality ?? '—' }}</td>
                <td>{{ $venue->is_active ? 'Sim' : 'Não' }}</td>
                <td>
                    <a href="{{ route('admin.venues.edit', $venue) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" style="display:inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn-ghost" style="color:#c0392b;border:0;padding:0 0 0 8px">Remover</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $venues->links() }}</div>
@endsection
