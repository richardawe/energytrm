<x-app-layout><x-slot name="title">Add Address — {{ $party->short_name }}</x-slot>
<div class="mb-3">
    <a href="{{ route('master.parties.show', $party) }}" class="text-muted small text-decoration-none">← {{ $party->short_name }}</a>
</div>
<div class="card card-etrm" style="max-width:640px;"><div class="card-header">Add Address — {{ $party->long_name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.parties.addresses.store', $party) }}">@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Address Type *</label>
        <select name="address_type" class="form-select @error('address_type') is-invalid @enderror" required>
            @foreach(['Main','Backup','Registered','Billing'] as $t)
            <option value="{{ $t }}" {{ old('address_type','Main') == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        @error('address_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
            <label class="form-check-label">Set as default address</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Address Line 1 *</label>
        <input type="text" name="address_line1" class="form-control @error('address_line1') is-invalid @enderror" value="{{ old('address_line1') }}" required>
        @error('address_line1')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Address Line 2</label>
        <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">City *</label>
        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" required>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">State / Province</label>
        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Country *</label>
        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}" required>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="50">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Effective Date</label>
        <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Contact User</label>
        <select name="contact_user_id" class="form-select">
            <option value="">— None —</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ old('contact_user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <input type="text" name="description" class="form-control" value="{{ old('description') }}" maxlength="255">
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button>
    <a href="{{ route('master.parties.show', $party) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div></x-app-layout>
