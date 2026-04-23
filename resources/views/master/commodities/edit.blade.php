<x-app-layout>
    <x-slot name="title">Edit Commodity</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.commodities.show', $commodity) }}" class="text-muted small text-decoration-none">← {{ $commodity->name }}</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Commodity — {{ $commodity->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.commodities.update', $commodity) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $commodity->name) }}" maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Commodity Group <span class="text-danger">*</span></label>
                        <select name="commodity_group" class="form-select @error('commodity_group') is-invalid @enderror">
                            <option value="">— Select group —</option>
                            @foreach(['Energy', 'Metal', 'Agricultural', 'Other'] as $group)
                            <option value="{{ $group }}" {{ old('commodity_group', $commodity->commodity_group) === $group ? 'selected' : '' }}>{{ $group }}</option>
                            @endforeach
                        </select>
                        @error('commodity_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description', $commodity->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $commodity->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.commodities.show', $commodity) }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.commodities.destroy', $commodity) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this commodity?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
