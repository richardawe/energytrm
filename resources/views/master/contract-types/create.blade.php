<x-app-layout>
    <x-slot name="title">New Contract Type</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.contract-types.index') }}" class="text-muted small text-decoration-none">← Contract Types</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">New Contract Type</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.contract-types.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="100" placeholder="Fixed Price">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" maxlength="20" placeholder="FP" style="text-transform:uppercase;">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Incoterm</label>
                        <input type="text" name="incoterm" class="form-control @error('incoterm') is-invalid @enderror"
                               value="{{ old('incoterm') }}" maxlength="20" placeholder="FOB">
                        <div class="form-text">e.g. FOB, CIF, DES</div>
                        @error('incoterm')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Contract Type</button>
                    <a href="{{ route('master.contract-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
