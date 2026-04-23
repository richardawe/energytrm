<x-app-layout>
    <x-slot name="title">Edit Stress Scenario</x-slot>

    <div class="mb-3">
        <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
        <span class="text-muted small"> / </span>
        <a href="{{ route('risk.stress-scenarios.index') }}" class="text-muted small text-decoration-none">Stress Scenarios</a>
        <span class="text-muted small"> / </span>
        <a href="{{ route('risk.stress-scenarios.show', $stressScenario) }}" class="text-muted small text-decoration-none">{{ $stressScenario->name }}</a>
        <span class="text-muted small"> / </span>
        <span class="small fw-semibold">Edit</span>
    </div>

    <div class="card card-etrm" style="max-width:760px;">
        <div class="card-header fw-semibold">Edit Stress Scenario</div>
        <div class="card-body">
            <form method="POST" action="{{ route('risk.stress-scenarios.update', $stressScenario) }}">
                @csrf @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Scenario Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $stressScenario->name) }}" maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="2"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $stressScenario->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="is_active" value="1"
                                   {{ old('is_active', $stressScenario->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <hr>

                <div x-data="{ shocks: {{ json_encode($stressScenario->shocks->map(fn($s) => ['index_id' => (string)$s->index_id, 'price_shock_pct' => $s->price_shock_pct])) }} }">
                    <label class="form-label fw-semibold">Price Shocks</label>
                    <template x-for="(shock, i) in shocks" :key="i">
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-6">
                                <select :name="'shocks['+i+'][index_id]'" class="form-select" x-model="shock.index_id">
                                    <option value="">— Select Index —</option>
                                    @foreach($indices as $idx)
                                    <option value="{{ $idx->id }}">{{ $idx->index_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="number" :name="'shocks['+i+'][price_shock_pct]'" class="form-control"
                                           placeholder="-30" step="0.01" min="-100" max="500"
                                           x-model="shock.price_shock_pct">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        @click="shocks.splice(i,1)"
                                        x-show="shocks.length > 1">Remove</button>
                            </div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                            @click="shocks.push({index_id:'',price_shock_pct:''})">+ Add Index Shock</button>
                </div>

                @error('shocks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @error('shocks.*.index_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @error('shocks.*.price_shock_pct')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update Scenario</button>
                    <a href="{{ route('risk.stress-scenarios.show', $stressScenario) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
