<x-app-layout>
    <x-slot name="title">New Commodity</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.commodities.index') }}" class="text-muted small text-decoration-none">← Commodities</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">New Commodity</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.commodities.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="150" placeholder="Crude Oil">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Commodity Group <span class="text-danger">*</span></label>
                        <select name="commodity_group" class="form-select @error('commodity_group') is-invalid @enderror">
                            <option value="">— Select group —</option>
                            @foreach(['Energy', 'Metal', 'Agricultural', 'Other'] as $group)
                            <option value="{{ $group }}" {{ old('commodity_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                            @endforeach
                        </select>
                        @error('commodity_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Commodity</button>
                    <a href="{{ route('master.commodities.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
