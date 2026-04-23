<x-app-layout>
    <x-slot name="title">Edit Contract Type</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.contract-types.show', $contractType) }}" class="text-muted small text-decoration-none">← {{ $contractType->name }}</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Contract Type — {{ $contractType->code }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.contract-types.update', $contractType) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $contractType->name) }}" maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $contractType->code) }}" maxlength="20" style="text-transform:uppercase;">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Incoterm</label>
                        <input type="text" name="incoterm" class="form-control @error('incoterm') is-invalid @enderror"
                               value="{{ old('incoterm', $contractType->incoterm) }}" maxlength="20">
                        <div class="form-text">e.g. FOB, CIF, DES</div>
                        @error('incoterm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description', $contractType->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $contractType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.contract-types.show', $contractType) }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.contract-types.destroy', $contractType) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this contract type?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
