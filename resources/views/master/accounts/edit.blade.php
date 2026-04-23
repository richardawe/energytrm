<x-app-layout><x-slot name="title">Edit Account</x-slot>
<div class="mb-3">
    <a href="{{ route('master.accounts.show', $account) }}" class="text-muted small text-decoration-none">← {{ $account->account_number }}</a>
</div>
<div class="card card-etrm" style="max-width:700px;"><div class="card-header">Edit Account — {{ $account->account_number }}</div><div class="card-body">
<form method="POST" action="{{ route('master.accounts.update', $account) }}">@csrf @method('PATCH')
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
        <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $account->account_number) }}" maxlength="50" required>
        @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-7">
        <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
        <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" value="{{ old('account_name', $account->account_name) }}" maxlength="150" required>
        @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
        <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
            @foreach(['Nostro','Internal Nostro','Vostro','Internal Vostro','Margin','Other'] as $t)
            <option value="{{ $t }}" {{ old('account_type', $account->account_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['Authorized','Auth Pending','Do Not Use','Amendment Pending'] as $s)
            <option value="{{ $s }}" {{ old('status', $account->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Holding Party</label>
        <select name="holding_party_id" class="form-select @error('holding_party_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach($holdingParties as $p)
            <option value="{{ $p->id }}" {{ old('holding_party_id', $account->holding_party_id) == $p->id ? 'selected' : '' }}>{{ $p->short_name }} — {{ $p->long_name }}</option>
            @endforeach
        </select>
        @error('holding_party_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach($currencies as $c)
            <option value="{{ $c->id }}" {{ old('currency_id', $account->currency_id) == $c->id ? 'selected' : '' }}>{{ $c->code }} — {{ $c->name }}</option>
            @endforeach
        </select>
        @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Class</label>
        <input type="text" name="class" class="form-control @error('class') is-invalid @enderror" value="{{ old('class', $account->class) }}" maxlength="100">
        @error('class')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">General Ledger Account</label>
        <input type="text" name="general_ledger_account" class="form-control @error('general_ledger_account') is-invalid @enderror" value="{{ old('general_ledger_account', $account->general_ledger_account) }}" maxlength="100">
        @error('general_ledger_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Account Legal Name</label>
        <input type="text" name="account_legal_name" class="form-control @error('account_legal_name') is-invalid @enderror" value="{{ old('account_legal_name', $account->account_legal_name) }}" maxlength="200">
        @error('account_legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Country</label>
        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $account->country) }}" maxlength="100">
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Date Opened</label>
        <input type="date" name="date_opened" class="form-control @error('date_opened') is-invalid @enderror" value="{{ old('date_opened', $account->date_opened?->format('Y-m-d')) }}">
        @error('date_opened')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Date Closed</label>
        <input type="date" name="date_closed" class="form-control @error('date_closed') is-invalid @enderror" value="{{ old('date_closed', $account->date_closed?->format('Y-m-d')) }}">
        @error('date_closed')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $account->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 d-flex flex-wrap gap-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="on_balance_sheet" value="1" id="on_balance_sheet" {{ old('on_balance_sheet', $account->on_balance_sheet) ? 'checked' : '' }}>
            <label class="form-check-label" for="on_balance_sheet">On Balance Sheet</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="allow_multiple_units" value="1" id="allow_multiple_units" {{ old('allow_multiple_units', $account->allow_multiple_units) ? 'checked' : '' }}>
            <label class="form-check-label" for="allow_multiple_units">Allow Multiple Units</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sweep_enabled" value="1" id="sweep_enabled" {{ old('sweep_enabled', $account->sweep_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="sweep_enabled">Sweep Enabled</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
    <a href="{{ route('master.accounts.show', $account) }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.accounts.destroy', $account) }}" class="ms-auto d-inline" onsubmit="return confirm('Delete?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
</div>
</form>
</div></div>
</x-app-layout>
