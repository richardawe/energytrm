<x-app-layout><x-slot name="title">New Incoterm</x-slot>
<div class="mb-3"><a href="{{ route('master.incoterms.index') }}" class="text-muted small text-decoration-none">← Incoterms</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">New Incoterm</div><div class="card-body">
<form method="POST" action="{{ route('master.incoterms.store') }}">@csrf
<div class="row g-3">
    <div class="col-4"><label class="form-label fw-semibold">Code *</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" maxlength="10" style="text-transform:uppercase;" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-8"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div></div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button><a href="{{ route('master.incoterms.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
