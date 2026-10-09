@extends('layouts.app')
@section('title', 'Organizações — Admin VAGA')
@section('content')
    <div style="display:flex;align-items:center;gap:12px">
        <h1 style="margin-right:auto">Organizações</h1>
        <a class="btn" href="{{ route('admin.organizations.create') }}">Nova organização</a>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-top:12px">
        <thead><tr style="text-align:left;border-bottom:1px solid #e4dccb"><th style="padding:8px 6px">Nome</th><th>Conta dona</th><th>Promotores</th><th>Ativa</th><th></th></tr></thead>
        <tbody>
        @foreach ($organizations as $organization)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:8px 6px">{{ $organization->name }}</td>
                <td class="muted">{{ $organization->owner?->name ?? '—' }}</td>
                <td class="muted">{{ $organization->promoters_count }}</td>
                <td>{{ $organization->is_active ? 'Sim' : 'Não' }}</td>
                <td>
                    <a href="{{ route('admin.organizations.edit', $organization) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.organizations.destroy', $organization) }}" style="display:inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn-ghost" style="color:#c0392b;border:0;padding:0 0 0 8px">Remover</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $organizations->links() }}</div>
@endsection
