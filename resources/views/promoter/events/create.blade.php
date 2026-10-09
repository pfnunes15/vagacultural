@extends('layouts.app')
@section('title', 'Novo evento — VAGA')
@section('content')
    <h1>Novo evento</h1>
    <p class="muted">Formulário provisório (sem design final). Cobre os campos reais de um evento.</p>
    <form method="POST" action="{{ route('painel.eventos.store') }}" enctype="multipart/form-data" style="max-width:720px">
        @csrf
        @include('promoter.events._form', ['event' => null])
        <p style="margin-top:16px"><button type="submit">Submeter evento</button></p>
    </form>
@endsection
