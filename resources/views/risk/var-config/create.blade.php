<x-app-layout>
    <x-slot name="title">New VaR Configuration</x-slot>

    <div class="mb-3">
        <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
        <span class="text-muted small"> / </span>
        <a href="{{ route('risk.var-config.index') }}" class="text-muted small text-decoration-none">VaR Configuration</a>
        <span class="text-muted small"> / </span>
        <span class="small fw-semibold">New</span>
    </div>

    <div class="card card-etrm" style="max-width:600px;">
        <div class="card-header fw-semibold">New VaR Configuration</div>
        <div class="card-body">
            <form method="POST" action="{{ route('risk.var-config.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="100" placeholder="e.g. Standard 99% 1-Day VaR">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lookback Period (days) <span class="text-danger">*</span></label>
                        <input type="number" name="lookback_period_days"
                               class="form-control @error('lookback_period_days') is-invalid @enderror"
                               value="{{ old('lookback_period_days', 250) }}" min="1" max="2000">
                        <div class="form-text">Typically 250 (1 trading year)</div>
                        @error('lookback_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holding Period <span class="text-danger">*</span></label>
                        <select name="holding_period_days"
                                class="form-select @error('holding_period_days') is-invalid @enderror">
                            <option value="1"  {{ old('holding_period_days', '1') == '1'  ? 'selected' : '' }}>1-day</option>
                            <option value="10" {{ old('holding_period_days') == '10' ? 'selected' : '' }}>10-day (Basel)</option>
                        </select>
                        @error('holding_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">VaR Method <span class="text-danger">*</span></label>
                        <select name="var_method"
                                class="form-select @error('var_method') is-invalid @enderror">
                            <option value="Historical Simulation" {{ old('var_method', 'Historical Simulation') === 'Historical Simulation' ? 'selected' : '' }}>Historical Simulation</option>
                            <option value="Parametric"            {{ old('var_method') === 'Parametric'            ? 'selected' : '' }}>Parametric</option>
                            <option value="Monte Carlo"           {{ old('var_method') === 'Monte Carlo'           ? 'selected' : '' }}>Monte Carlo</option>
                        </select>
                        @error('var_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confidence Level <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="confidence_level"
                                   class="form-control @error('confidence_level') is-invalid @enderror"
                                   value="{{ old('confidence_level', '0.9900') }}"
                                   step="0.0001" min="0.9" max="0.9999"
                                   placeholder="0.9900">
                            <span class="input-group-text text-muted" style="font-size:.8rem;">e.g. 0.9500 = 95%</span>
                        </div>
                        @error('confidence_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Configuration</button>
                    <a href="{{ route('risk.var-config.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
