<x-app-layout><x-slot name="title">New Payment Term</x-slot>
<div class="mb-3"><a href="{{ route('master.payment-terms.index') }}" class="text-muted small text-decoration-none">← Payment Terms</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">New Payment Term</div><div class="card-body">
<form method="POST" action="{{ route('master.payment-terms.store') }}">@csrf
<div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Net 30, Net 60...">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label fw-semibold">Days Net *</label><input type="number" name="days_net" class="form-control" value="{{ old('days_net', 30) }}" min="0" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Description</label><input type="text" name="description" class="form-control" value="{{ old('description') }}"></div>
<div class="mb-3 form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div>
<div class="d-flex gap-2"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button><a href="{{ route('master.payment-terms.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
