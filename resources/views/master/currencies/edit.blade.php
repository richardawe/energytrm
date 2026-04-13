<x-app-layout>
    <x-slot name="title">Edit Currency</x-slot>
    <div class="mb-3">
        <a href="{{ route('master.currencies.index') }}" class="text-muted small text-decoration-none">← Currencies</a>
    </div>
    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Currency — {{ $currency->code }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.currencies.update', $currency) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $currency->code) }}" maxlength="3" style="text-transform:uppercase;">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $currency->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Symbol</label>
                        <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $currency->symbol) }}" maxlength="10">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">FX Rate to USD <span class="text-danger">*</span></label>
                        <input type="number" name="fx_rate_to_usd" class="form-control @error('fx_rate_to_usd') is-invalid @enderror" value="{{ old('fx_rate_to_usd', $currency->fx_rate_to_usd) }}" step="0.00000001" min="0">
                        @error('fx_rate_to_usd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.currencies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.currencies.destroy', $currency) }}" class="ms-auto" onsubmit="return confirm('Delete this currency?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
