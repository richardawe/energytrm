<x-app-layout>
    <x-slot name="title">Edit VaR Configuration</x-slot>

    <div class="mb-3">
        <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
        <span class="text-muted small"> / </span>
        <a href="{{ route('risk.var-config.index') }}" class="text-muted small text-decoration-none">VaR Configuration</a>
        <span class="text-muted small"> / </span>
        <span class="small fw-semibold">Edit</span>
    </div>

    <div class="card card-etrm" style="max-width:600px;">
        <div class="card-header fw-semibold">Edit VaR Configuration</div>
        <div class="card-body">
            <form method="POST" action="{{ route('risk.var-config.update', $varConfig) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $varConfig->name) }}" maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lookback Period (days) <span class="text-danger">*</span></label>
                        <input type="number" name="lookback_period_days"
                               class="form-control @error('lookback_period_days') is-invalid @enderror"
                               value="{{ old('lookback_period_days', $varConfig->lookback_period_days) }}"
                               min="1" max="2000">
                        <div class="form-text">Typically 250 (1 trading year)</div>
                        @error('lookback_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holding Period <span class="text-danger">*</span></label>
                        <select name="holding_period_days"
                                class="form-select @error('holding_period_days') is-invalid @enderror">
                            @php $hp = old('holding_period_days', $varConfig->holding_period_days); @endphp
                            <option value="1"  {{ $hp == '1'  ? 'selected' : '' }}>1-day</option>
                            <option value="10" {{ $hp == '10' ? 'selected' : '' }}>10-day (Basel)</option>
                        </select>
                        @error('holding_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">VaR Method <span class="text-danger">*</span></label>
                        <select name="var_method"
                                class="form-select @error('var_method') is-invalid @enderror">
                            @php $method = old('var_method', $varConfig->var_method); @endphp
                            <option value="Historical Simulation" {{ $method === 'Historical Simulation' ? 'selected' : '' }}>Historical Simulation</option>
                            <option value="Parametric"            {{ $method === 'Parametric'            ? 'selected' : '' }}>Parametric</option>
                            <option value="Monte Carlo"           {{ $method === 'Monte Carlo'           ? 'selected' : '' }}>Monte Carlo</option>
                        </select>
                        @error('var_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confidence Level <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="confidence_level"
                                   class="form-control @error('confidence_level') is-invalid @enderror"
                                   value="{{ old('confidence_level', $varConfig->confidence_level) }}"
                                   step="0.0001" min="0.9" max="0.9999">
                            <span class="input-group-text text-muted" style="font-size:.8rem;">e.g. 0.9500 = 95%</span>
                        </div>
                        @error('confidence_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="is_active" value="1"
                                   {{ old('is_active', $varConfig->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update Configuration</button>
                    <a href="{{ route('risk.var-config.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
