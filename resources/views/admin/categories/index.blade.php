@extends('layouts.app')
@section('title', 'Categorias — Admin VAGA')
@section('content')
    <div style="display:flex;align-items:center;gap:12px">
        <h1 style="margin-right:auto">Categorias</h1>
        <a class="btn" href="{{ route('admin.categories.create') }}">Nova categoria</a>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-top:12px">
        <thead><tr style="text-align:left;border-bottom:1px solid #e4dccb"><th style="padding:8px 6px">Nome</th><th>Slug</th><th>Pai</th><th>Ativa</th><th></th></tr></thead>
        <tbody>
        @foreach ($categories as $category)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:8px 6px">@if($category->color)<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $category->color }}"></span>@endif {{ $category->name }}</td>
                <td class="muted">{{ $category->slug }}</td>
                <td class="muted">{{ $category->parent?->name ?? '—' }}</td>
                <td>{{ $category->is_active ? 'Sim' : 'Não' }}</td>
                <td>
                    <a href="{{ route('admin.categories.edit', $category) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn-ghost" style="color:#c0392b;border:0;padding:0 0 0 8px">Remover</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $categories->links() }}</div>
@endsection
