<x-app-layout><x-slot name="title">New Index</x-slot>
<div class="mb-3"><a href="{{ route('master.indices.index') }}" class="text-muted small text-decoration-none">← Indices</a></div>
<div class="card card-etrm" style="max-width:640px;"><div class="card-header">New Index / Curve</div><div class="card-body">
<form method="POST" action="{{ route('master.indices.store') }}">@csrf
<div class="row g-3">
    <div class="col-12"><label class="form-label fw-semibold">Index Name *</label><input type="text" name="index_name" class="form-control @error('index_name') is-invalid @enderror" value="{{ old('index_name') }}" required placeholder="Brent 1M, TTF Day-Ahead...">@error('index_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label fw-semibold">Market</label><input type="text" name="market" class="form-control" value="{{ old('market') }}" placeholder="Crude Oil, Natural Gas, Power"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Index Group</label><input type="text" name="index_group" class="form-control" value="{{ old('index_group') }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Format *</label><select name="format" class="form-select" required><option value="Monthly" selected>Monthly</option><option value="Daily">Daily</option><option value="Quarterly">Quarterly</option><option value="Annual">Annual</option></select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Class</label><input type="text" name="class" class="form-control" value="{{ old('class') }}" placeholder="Energy, Metal..."></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Base Currency</label><select name="base_currency_id" class="form-select"><option value="">— None —</option>@foreach($currencies as $c)<option value="{{ $c->id }}" {{ old('base_currency_id')==$c->id ? 'selected' : '' }}>{{ $c->code }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">UOM</label><select name="uom_id" class="form-select"><option value="">— None —</option>@foreach($uoms as $u)<option value="{{ $u->id }}" {{ old('uom_id')==$u->id ? 'selected' : '' }}>{{ $u->code }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Index Status *</label><select name="status" class="form-select" required><option value="Custom" selected>Custom</option><option value="Official">Official</option><option value="Template">Template</option></select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Record Status *</label><select name="rec_status" class="form-select" required><option value="Authorized" selected>Authorized</option><option value="Auth Pending">Auth Pending</option><option value="Do Not Use">Do Not Use</option></select></div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Create Index</button><a href="{{ route('master.indices.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
