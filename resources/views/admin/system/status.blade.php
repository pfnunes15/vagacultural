@extends('layouts.app')
@section('title', 'Estado do sistema — Admin VAGA')
@section('content')
    <h1>Estado do sistema / APIs</h1>
    <table style="width:100%;border-collapse:collapse;margin-top:12px">
        <thead><tr style="text-align:left;border-bottom:1px solid #e4dccb">
            <th style="padding:8px 6px">Serviço</th><th>Estado</th><th>Detalhe</th><th>Latência</th>
        </tr></thead>
        <tbody>
        @foreach ($checks as $check)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:8px 6px"><strong>{{ $check['name'] }}</strong></td>
                <td>@if ($check['ok'])<span class="tag" style="background:#e8f5e9;color:#0e5b4f">OK</span>@else<span class="tag" style="background:#fde8e6;color:#c0392b">Falha</span>@endif</td>
                <td class="muted">{{ $check['detail'] }}</td>
                <td class="muted">{{ $check['ms'] }} ms</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="muted" style="margin-top:12px">Atualizado agora · {{ now()->format('H:i:s') }}</p>
@endsection
