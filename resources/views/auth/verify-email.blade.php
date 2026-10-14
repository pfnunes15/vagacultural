@extends('layouts.app')
@section('title', 'Confirma o teu e-mail — VAGA')
@section('content')
    <div class="max-w-md mx-auto vcard p-7 mt-6">
        <h1 class="font-display text-2xl font-bold">Confirma o teu e-mail</h1>
        <p class="text-[var(--color-muted)] mt-2">Enviámos-te um link de confirmação. Verifica a tua caixa de entrada.</p>
        <form method="POST" action="{{ route('verification.send') }}" class="mt-4">@csrf<button class="btn-primary">Reenviar e-mail</button></form>
    </div>
@endsection
