@extends('layouts.app')
@section('title', 'Utilizadores — Admin VAGA')
@section('content')
    <h1>Utilizadores</h1>

    @error('roles')<div class="notice">{{ $message }}</div>@enderror
    @error('trust')<div class="notice">{{ $message }}</div>@enderror
    @error('impersonate')<div class="notice">{{ $message }}</div>@enderror

    <form method="GET" style="margin:12px 0;display:flex;gap:8px;max-width:420px">
        <input name="q" value="{{ $term }}" placeholder="Procurar por nome ou email">
        <button class="btn-ghost">Procurar</button>
    </form>

    @foreach ($users as $user)
        @php $promoter = $user->promoterProfile; @endphp
        <div class="card" style="margin-bottom:12px">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <div style="margin-right:auto">
                    <strong>{{ $user->name }}</strong> <span class="muted">{{ $user->email }}</span>
                    @if ($user->email_verified_at)<span class="tag">verificado</span>@else<span class="tag" style="background:#fff4e5;color:#cf9427">por verificar</span>@endif
                    <div class="muted">papéis: {{ $user->roles->pluck('role')->map(fn($r)=>$r->value)->implode(', ') ?: '—' }}</div>
                </div>

                {{-- roles --}}
                <form method="POST" action="{{ route('admin.users.roles', $user) }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    @csrf
                    @foreach ($allRoles as $role)
                        <label style="font-weight:400;display:flex;gap:4px;align-items:center">
                            <input type="checkbox" name="roles[]" value="{{ $role->value }}" style="width:auto"
                                @checked($user->roles->contains(fn($r)=>$r->role===$role))> {{ $role->label() }}
                        </label>
                    @endforeach
                    <button class="btn-ghost">Guardar papéis</button>
                </form>
            </div>

            <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
                <form method="POST" action="{{ route('admin.users.resend', $user) }}">@csrf<button class="btn-ghost">Reenviar e-mail de registo</button></form>
                @if ($promoter)
                    <form method="POST" action="{{ route('admin.users.trust', $user) }}">@csrf<button class="btn-ghost">{{ $promoter->auto_publish ? 'Desativar' : 'Ativar' }} publicação automática</button></form>
                @endif
                @if (($user->isPromoter() || $user->isOrganization()) && ! $user->isAdmin())
                    <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">@csrf<button class="btn">Entrar como</button></form>
                @endif
            </div>
        </div>
    @endforeach
    <div>{{ $users->links() }}</div>
@endsection
