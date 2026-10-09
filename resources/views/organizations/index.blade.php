@extends('layouts.app')
@section('title', 'Organizações — VAGA')
@section('content')
    <h1>Organizações</h1>
    @if ($organizations->isEmpty())
        <div class="card">Sem organizações.</div>
    @else
        <div class="grid">
            @foreach ($organizations as $organization)
                <article class="card">
                    <h3 style="margin:0 0 4px"><a href="{{ route('organizations.show', $organization) }}">{{ $organization->name }}</a></h3>
                    @if ($organization->description)<p class="muted">{{ \Illuminate\Support\Str::limit($organization->description, 120) }}</p>@endif
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $organizations->links() }}</div>
    @endif
@endsection
