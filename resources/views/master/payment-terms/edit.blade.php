<x-app-layout><x-slot name="title">Edit Payment Term</x-slot>
<div class="mb-3"><a href="{{ route('master.payment-terms.index') }}" class="text-muted small text-decoration-none">← Payment Terms</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">Edit — {{ $paymentTerm->name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.payment-terms.update', $paymentTerm) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $paymentTerm->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label fw-semibold">Days Net *</label><input type="number" name="days_net" class="form-control" value="{{ old('days_net', $paymentTerm->days_net) }}" min="0" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Description</label><input type="text" name="description" class="form-control" value="{{ old('description', $paymentTerm->description) }}"></div>
<div class="mb-3 form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentTerm->is_active) ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.payment-terms.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.payment-terms.destroy', $paymentTerm) }}" class="ms-auto" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button></form>
</div></form></div></div></x-app-layout>
