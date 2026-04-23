<x-app-layout>
    <x-slot name="title">New Credit Warning Threshold</x-slot>

    <div class="mb-3">
        <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
        <span class="text-muted small"> / </span>
        <a href="{{ route('risk.credit-warnings.index') }}" class="text-muted small text-decoration-none">Credit Warning Thresholds</a>
        <span class="text-muted small"> / </span>
        <span class="small fw-semibold">New</span>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header fw-semibold">New Credit Warning Threshold</div>
        <div class="card-body">
            <form method="POST" action="{{ route('risk.credit-warnings.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                        <select name="party_id"
                                class="form-select @error('party_id') is-invalid @enderror">
                            <option value="">— Select Counterparty —</option>
                            @foreach($parties as $party)
                            <option value="{{ $party->id }}" {{ old('party_id') == $party->id ? 'selected' : '' }}>
                                {{ $party->short_name }}@if($party->long_name) — {{ $party->long_name }}@endif
                            </option>
                            @endforeach
                        </select>
                        @error('party_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Warning Threshold <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="warning_threshold_pct"
                                   class="form-control @error('warning_threshold_pct') is-invalid @enderror"
                                   value="{{ old('warning_threshold_pct', '80.00') }}"
                                   step="0.01" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text text-warning">Triggers a near-limit alert</div>
                        @error('warning_threshold_pct')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Breach Threshold <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="breach_threshold_pct"
                                   class="form-control @error('breach_threshold_pct') is-invalid @enderror"
                                   value="{{ old('breach_threshold_pct', '100.00') }}"
                                   step="0.01" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text text-danger">Triggers a BREACH flag</div>
                        @error('breach_threshold_pct')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Threshold</button>
                    <a href="{{ route('risk.credit-warnings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
