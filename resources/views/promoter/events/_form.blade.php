@php
    $occs = ($event ?? null) ? $event->occurrences : collect();
    $tiers = ($event ?? null) ? $event->ticketTiers : collect();
    $rows = max(3, $occs->count());
    $tierRows = max(3, $tiers->count());
@endphp

@if (! ($event ?? null))
    <label for="promoter_id">Publicar como</label>
    <select id="promoter_id" name="promoter_id" required style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
        @foreach ($promoters as $promoter)
            <option value="{{ $promoter->id }}">{{ $promoter->name }}@if ($promoter->auto_publish) (publicação automática)@endif</option>
        @endforeach
    </select>
    @error('promoter_id')<div class="err">{{ $message }}</div>@enderror
@endif

<label for="title">Título</label>
<input id="title" name="title" value="{{ old('title', $event?->title) }}" required>
@error('title')<div class="err">{{ $message }}</div>@enderror

<label for="summary">Descrição curta</label>
<input id="summary" name="summary" maxlength="500" value="{{ old('summary', $event?->summary) }}">

<label for="description">Descrição completa</label>
<textarea id="description" name="description" rows="5" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">{{ old('description', $event?->description) }}</textarea>

<label for="cover_image">Imagem de capa <span class="muted">(1080×1350 px — Instagram 4:5)@if ($event?->cover_image_path) · já tem capa, envia só para substituir @endif</span></label>
<input id="cover_image" name="cover_image" type="file" accept="image/*">
@error('cover_image')<div class="err">{{ $message }}</div>@enderror

<label for="min_age">Idade mínima</label>
<select id="min_age" name="min_age" style="width:100%;padding:9px;border:1px solid #cbccc9;border-radius:8px">
    <option value="">— não aplicável —</option>
    @foreach ($ageRatings as $age)
        <option value="{{ $age->value }}" @selected((string) old('min_age', $event?->min_age?->value) === (string) $age->value)>{{ $age->label() }}</option>
    @endforeach
</select>

<label>Categorias (uma ou mais)</label>
<div style="display:flex;flex-wrap:wrap;gap:10px">
    @foreach ($categories as $cat)
        <label style="font-weight:400;display:flex;gap:6px;align-items:center">
            <input type="checkbox" name="categories[]" value="{{ $cat->id }}" style="width:auto"
                @checked(in_array($cat->id, old('categories', ($event ?? null) ? $event->categories->pluck('id')->all() : [])))> {{ $cat->name }}
        </label>
    @endforeach
</div>
@error('categories')<div class="err">{{ $message }}</div>@enderror

<label for="tags">Tags <span class="muted">(separadas por vírgula)</span></label>
<input id="tags" name="tags" value="{{ old('tags', ($event ?? null) ? $event->tags->pluck('name')->implode(', ') : '') }}" placeholder="ex: ao ar livre, família, jazz">

<fieldset style="margin-top:16px;border:1px solid #e4dccb;border-radius:8px;padding:12px">
    <legend>Bilhetes</legend>
    <label style="font-weight:400;display:flex;gap:8px;align-items:center">
        <input type="checkbox" name="is_free" value="1" style="width:auto" @checked(old('is_free', $event?->is_free))> Entrada livre
    </label>
    <label for="ticket_url">Link de bilhetes (externo, opcional)</label>
    <input id="ticket_url" name="ticket_url" type="url" value="{{ old('ticket_url', $event?->ticket_url) }}" placeholder="https://">
    <p class="muted" style="margin-top:10px">Preços por escalão (opcional — pode variar por idade):</p>
    @for ($i = 0; $i < $tierRows; $i++)
        @php $t = $tiers->values()->get($i); @endphp
        <div style="display:flex;gap:8px;margin-bottom:6px;flex-wrap:wrap">
            <input name="tiers[{{ $i }}][name]" value="{{ $t?->name }}" placeholder="Escalão (ex: Adulto)" style="flex:2;min-width:140px">
            <input name="tiers[{{ $i }}][price]" value="{{ $t?->price }}" type="number" step="0.01" min="0" placeholder="€" style="flex:1;min-width:80px">
            <input name="tiers[{{ $i }}][min_age]" value="{{ $t?->min_age }}" type="number" min="0" placeholder="idade mín." style="flex:1;min-width:90px">
            <input name="tiers[{{ $i }}][max_age]" value="{{ $t?->max_age }}" type="number" min="0" placeholder="idade máx." style="flex:1;min-width:90px">
        </div>
    @endfor
</fieldset>

<fieldset style="margin-top:16px;border:1px solid #e4dccb;border-radius:8px;padding:12px">
    <legend>Data e local</legend>
    <p class="muted">Data única, várias datas separadas, ou intervalo contínuo (marca "dia inteiro" + fim).</p>
    @for ($i = 0; $i < $rows; $i++)
        @php $o = $occs->values()->get($i); @endphp
        <div style="border:1px dashed #e4dccb;border-radius:8px;padding:10px;margin-bottom:10px">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <div><label>Início @if($i>0)<span class="muted">(opcional)</span>@endif</label><input name="occurrences[{{ $i }}][starts_at]" type="datetime-local" value="{{ $o?->starts_at?->format('Y-m-d\TH:i') }}" @if($i===0 && ! ($event ?? null)) required @endif></div>
                <div><label>Fim</label><input name="occurrences[{{ $i }}][ends_at]" type="datetime-local" value="{{ $o?->ends_at?->format('Y-m-d\TH:i') }}"></div>
                <label style="font-weight:400;display:flex;gap:6px;align-items:center;align-self:flex-end">
                    <input type="checkbox" name="occurrences[{{ $i }}][is_all_day]" value="1" style="width:auto" @checked($o?->is_all_day)> dia inteiro / contínuo
                </label>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
                <label style="font-weight:400;display:flex;gap:6px;align-items:center">
                    <input type="checkbox" name="occurrences[{{ $i }}][is_online]" value="1" style="width:auto" @checked($o?->is_online)> Online
                </label>
                <input name="occurrences[{{ $i }}][online_url]" type="url" value="{{ $o?->online_url }}" placeholder="Link (se online)" style="flex:1;min-width:160px">
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
                <select name="occurrences[{{ $i }}][venue_id]" style="flex:1;min-width:160px;padding:9px;border:1px solid #cbccc9;border-radius:8px">
                    <option value="">— local (opcional) —</option>
                    @foreach ($venues as $venue)<option value="{{ $venue->id }}" @selected($o?->venue_id==$venue->id)>{{ $venue->name }}</option>@endforeach
                </select>
                <input name="occurrences[{{ $i }}][address]" value="{{ $o?->address }}" placeholder="Morada (se não for um local)" style="flex:2;min-width:160px">
                <input name="occurrences[{{ $i }}][postal_code]" value="{{ $o?->postal_code }}" placeholder="Código postal" style="flex:1;min-width:100px">
            </div>
        </div>
    @endfor
</fieldset>
