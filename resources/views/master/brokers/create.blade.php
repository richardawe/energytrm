<x-app-layout><x-slot name="title">New Broker</x-slot>
<div class="mb-3"><a href="{{ route('master.brokers.index') }}" class="text-muted small text-decoration-none">← Brokers</a></div>
<div class="card card-etrm" style="max-width:560px;"><div class="card-header">New Broker</div><div class="card-body">
<form method="POST" action="{{ route('master.brokers.store') }}">@csrf
<div class="row g-3">
    <div class="col-md-8"><label class="form-label fw-semibold">Broker Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label fw-semibold">Short Name</label><input type="text" name="short_name" class="form-control" value="{{ old('short_name') }}" maxlength="20" placeholder="ICAP, MRX..."></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Type *</label><select name="broker_type" class="form-select" required><option value="Voice" {{ old('broker_type','Voice')=='Voice' ? 'selected' : '' }}>Voice</option><option value="Electronic" {{ old('broker_type')=='Electronic' ? 'selected' : '' }}>Electronic</option><option value="Hybrid" {{ old('broker_type')=='Hybrid' ? 'selected' : '' }}>Hybrid</option></select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required>@foreach(['Active','Suspended','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('status','Active')==$s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">LEI</label><input type="text" name="lei" class="form-control @error('lei') is-invalid @enderror" value="{{ old('lei') }}" maxlength="20" placeholder="20-char ISO 17442">@error('lei')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_regulated" value="1" {{ old('is_regulated') ? 'checked' : '' }}><label class="form-check-label">Regulated Entity (FCA/CFTC registered)</label></div></div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button><a href="{{ route('master.brokers.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
