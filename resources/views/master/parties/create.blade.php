<x-app-layout><x-slot name="title">New Party</x-slot>
<div class="mb-3"><a href="{{ route('master.parties.index') }}" class="text-muted small text-decoration-none">← Parties</a></div>
<div class="card card-etrm" style="max-width:720px;"><div class="card-header">New Party</div><div class="card-body">
<form method="POST" action="{{ route('master.parties.store') }}">@csrf
<div class="form-section-title">Identity</div>
<div class="row g-3 mb-3">
    <div class="col-md-4"><label class="form-label fw-semibold">Type *</label><select name="party_type" class="form-select @error('party_type') is-invalid @enderror" required><option value="">— Select —</option><option value="Group" {{ old('party_type')=='Group' ? 'selected' : '' }}>Party Group</option><option value="LE" {{ old('party_type')=='LE' ? 'selected' : '' }}>Legal Entity</option><option value="BU" {{ old('party_type')=='BU' ? 'selected' : '' }}>Business Unit</option></select>@error('party_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label fw-semibold">Internal / External *</label><select name="internal_external" class="form-select" required><option value="External" {{ old('internal_external','External')=='External' ? 'selected' : '' }}>External</option><option value="Internal" {{ old('internal_external')=='Internal' ? 'selected' : '' }}>Internal</option></select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Parent</label><select name="parent_id" class="form-select"><option value="">— None —</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id ? 'selected' : '' }}>{{ $parent->short_name }} ({{ $parent->party_type }})</option>@endforeach</select></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4"><label class="form-label fw-semibold">Short Name *</label><input type="text" name="short_name" class="form-control @error('short_name') is-invalid @enderror" value="{{ old('short_name') }}" maxlength="32" required>@error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-8"><label class="form-label fw-semibold">Long Name *</label><input type="text" name="long_name" class="form-control @error('long_name') is-invalid @enderror" value="{{ old('long_name') }}" required>@error('long_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required><option value="Auth Pending" {{ old('status','Auth Pending')=='Auth Pending' ? 'selected' : '' }}>Auth Pending</option><option value="Authorized" {{ old('status')=='Authorized' ? 'selected' : '' }}>Authorized</option><option value="Do Not Use">Do Not Use</option></select></div>
</div>
<div class="form-section-title">Regulatory & Compliance</div>
<div class="row g-3 mb-3">
    <div class="col-md-4"><label class="form-label fw-semibold">LEI</label><input type="text" name="lei" class="form-control" value="{{ old('lei') }}" maxlength="20" placeholder="20-char ISO 17442"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">BIC / SWIFT</label><input type="text" name="bic_swift" class="form-control" value="{{ old('bic_swift') }}" maxlength="11"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Regulatory Class</label><select name="regulatory_class" class="form-select"><option value="">— None —</option><option value="FC">FC</option><option value="NFC">NFC</option><option value="NFC+">NFC+</option><option value="Third-Country">Third-Country</option></select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Credit Limit</label><input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit') }}" min="0" step="0.01"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Credit Limit Currency</label><select name="credit_limit_currency_id" class="form-select"><option value="">— Select —</option>@foreach($currencies as $c)<option value="{{ $c->id }}" {{ old('credit_limit_currency_id')==$c->id ? 'selected' : '' }}>{{ $c->code }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">KYC Status</label><select name="kyc_status" class="form-select"><option value="">— None —</option><option value="Pending">Pending</option><option value="Approved">Approved</option><option value="Expired">Expired</option><option value="Suspended">Suspended</option></select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">KYC Review Date</label><input type="date" name="kyc_review_date" class="form-control" value="{{ old('kyc_review_date') }}"></div>
</div>
<div class="d-flex gap-2"><button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Party</button><a href="{{ route('master.parties.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form></div></div></x-app-layout>
