<x-app-layout>
    <x-slot name="title">Edit Commission — {{ $commission->name }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.brokers.show', $broker) }}" class="text-muted small text-decoration-none">← {{ $broker->name }}</a>
    </div>

    <div class="card card-etrm" style="max-width:640px;">
        <div class="card-header">Edit Commission Schedule — {{ $commission->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.brokers.commissions.update', $commission) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Schedule Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $commission->name) }}" maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commission Rate <span class="text-danger">*</span></label>
                        <input type="number" name="commission_rate" step="0.000001" min="0"
                               class="form-control @error('commission_rate') is-invalid @enderror"
                               value="{{ old('commission_rate', $commission->commission_rate) }}">
                        @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rate Unit</label>
                        <input type="text" name="rate_unit" class="form-control"
                               value="{{ old('rate_unit', $commission->rate_unit) }}" maxlength="50">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Currency</label>
                        <select name="currency_id" class="form-select">
                            <option value="">— none —</option>
                            @foreach($currencies as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('currency_id', $commission->currency_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Frequency <span class="text-danger">*</span></label>
                        <select name="payment_frequency" class="form-select @error('payment_frequency') is-invalid @enderror">
                            @foreach(['Per Trade','Monthly','Quarterly'] as $f)
                                <option value="{{ $f }}"
                                    {{ old('payment_frequency', $commission->payment_frequency) == $f ? 'selected' : '' }}>
                                    {{ $f }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Min Fee</label>
                        <input type="number" name="min_fee" step="0.01" min="0" class="form-control"
                               value="{{ old('min_fee', $commission->min_fee) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Max Fee</label>
                        <input type="number" name="max_fee" step="0.01" min="0" class="form-control"
                               value="{{ old('max_fee', $commission->max_fee) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Index Group</label>
                        <input type="text" name="index_group" class="form-control"
                               value="{{ old('index_group', $commission->index_group) }}" maxlength="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Effective Date</label>
                        <input type="date" name="effective_date" class="form-control"
                               value="{{ old('effective_date', $commission->effective_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                   {{ old('is_default', $commission->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label">Set as default commission schedule for this broker</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
                    <a href="{{ route('master.brokers.show', $broker) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
