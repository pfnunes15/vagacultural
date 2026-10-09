@extends('layouts.app')
@section('title', 'Promotores — VAGA')
@section('content')
    <h1>Promotores</h1>
    @if ($promoters->isEmpty())
        <div class="card">Sem promotores.</div>
    @else
        <div class="grid">
            @foreach ($promoters as $promoter)
                <article class="card">
                    <h3 style="margin:0 0 4px"><a href="{{ route('promoters.show', $promoter) }}">{{ $promoter->name }}</a></h3>
                    @if ($promoter->is_verified)<span class="tag">verificado</span>@endif
                    @if ($promoter->description)<p class="muted">{{ \Illuminate\Support\Str::limit($promoter->description, 120) }}</p>@endif
                </article>
            @endforeach
        </div>
        <div style="margin-top:20px">{{ $promoters->links() }}</div>
    @endif
@endsection
