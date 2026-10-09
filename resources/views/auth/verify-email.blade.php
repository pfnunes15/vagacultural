@extends('layouts.app')
@section('title', 'Confirma o teu e-mail — VAGA')
@section('content')
    <h1>Confirma o teu e-mail</h1>
    <p>Enviámos-te um link de confirmação. Verifica a tua caixa de entrada.</p>
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn">Reenviar e-mail</button></form>
@endsection
