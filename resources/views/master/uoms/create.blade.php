<x-app-layout><x-slot name="title">New UOM</x-slot>
<div class="mb-3"><a href="{{ route('master.uoms.index') }}" class="text-muted small text-decoration-none">← Units of Measure</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">New Unit of Measure</div><div class="card-body">
<form method="POST" action="{{ route('master.uoms.store') }}">@csrf
<div class="row g-3">
    <div class="col-6"><label class="form-label fw-semibold">Code *</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" maxlength="20" required placeholder="MT, BBL, MMBTU">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-6"><label class="form-label fw-semibold">Base Unit</label><input type="text" name="base_unit" class="form-control" value="{{ old('base_unit') }}" maxlength="20"></div>
    <div class="col-12"><label class="form-label fw-semibold">Description *</label><input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" required></div>
    <div class="col-12"><label class="form-label fw-semibold">Conversion Factor *</label><input type="number" name="conversion_factor" class="form-control" value="{{ old('conversion_factor', 1) }}" step="0.00000001" min="0" required></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div></div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button><a href="{{ route('master.uoms.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
