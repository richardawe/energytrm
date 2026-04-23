<x-app-layout><x-slot name="title">New Settlement Instruction</x-slot>
<div class="mb-3">
    <a href="{{ route('master.settlement-instructions.index') }}" class="text-muted small text-decoration-none">← Settlement Instructions</a>
</div>
<div class="card card-etrm" style="max-width:720px;"><div class="card-header">New Settlement Instruction</div><div class="card-body">
<form method="POST" action="{{ route('master.settlement-instructions.store') }}">@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Name *</label>
        <input type="text" name="si_name" class="form-control @error('si_name') is-invalid @enderror" value="{{ old('si_name') }}" maxlength="150" required>
        @error('si_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Status *</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['Auth Pending','Authorized','Amendment Pending','Do Not Use'] as $s)
            <option value="{{ $s }}" {{ old('status','Auth Pending') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Party</label>
        <select name="party_id" class="form-select">
            <option value="">— None —</option>
            @foreach($parties as $p)
            <option value="{{ $p->id }}" {{ old('party_id') == $p->id ? 'selected' : '' }}>{{ $p->short_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Settler</label>
        <input type="text" name="settler" class="form-control" value="{{ old('settler') }}" maxlength="100">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Payment Method</label>
        <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method') }}" maxlength="100" placeholder="e.g. SWIFT, CHAPS, FedWire">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Advice</label>
        <input type="text" name="advice" class="form-control" value="{{ old('advice') }}" maxlength="100">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Account Name</label>
        <input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}" maxlength="150">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">End Date</label>
        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Linked Settlement Instruction</label>
        <select name="link_settle_id" class="form-select">
            <option value="">— None —</option>
            @foreach($linked as $l)
            <option value="{{ $l->id }}" {{ old('link_settle_id') == $l->id ? 'selected' : '' }}>{{ $l->si_number }} — {{ $l->si_name }}</option>
            @endforeach
        </select>
        <div class="form-text">For amendments — link to the original SI.</div>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="is_dvp" value="1" {{ old('is_dvp') ? 'checked' : '' }}>
            <label class="form-check-label">Delivery vs Payment (DVP)</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button>
    <a href="{{ route('master.settlement-instructions.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div></x-app-layout>
