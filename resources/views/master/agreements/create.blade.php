<x-app-layout><x-slot name="title">New Agreement</x-slot>
<div class="mb-3"><a href="{{ route('master.agreements.index') }}" class="text-muted small text-decoration-none">← Agreements</a></div>
<div class="card card-etrm" style="max-width:640px;"><div class="card-header">New Agreement</div><div class="card-body">
<form method="POST" action="{{ route('master.agreements.store') }}">@csrf
<div class="mb-3"><label class="form-label fw-semibold">Agreement Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label fw-semibold">Internal Party</label><select name="internal_party_id" class="form-select"><option value="">— Select —</option>@foreach($internalParties as $p)<option value="{{ $p->id }}" {{ old('internal_party_id')==$p->id ? 'selected' : '' }}>{{ $p->short_name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Counterparty</label><select name="counterparty_id" class="form-select"><option value="">— Select —</option>@foreach($counterparties as $p)<option value="{{ $p->id }}" {{ old('counterparty_id')==$p->id ? 'selected' : '' }}>{{ $p->short_name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Payment Terms</label><select name="payment_terms_id" class="form-select"><option value="">— None —</option>@foreach($paymentTerms as $pt)<option value="{{ $pt->id }}" {{ old('payment_terms_id')==$pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Effective Date</label><input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}"></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required>@foreach(['Authorized','Auth Pending','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('status','Authorized')==$s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
</div>
<div class="d-flex gap-2"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button><a href="{{ route('master.agreements.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
