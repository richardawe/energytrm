<x-app-layout>
    <x-slot name="title">Edit Governing Body</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.governing-bodies.show', $governingBody) }}" class="text-muted small text-decoration-none">← {{ $governingBody->name }}</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Governing Body — {{ $governingBody->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.governing-bodies.update', $governingBody) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $governingBody->name) }}" maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jurisdiction</label>
                        <input type="text" name="jurisdiction" class="form-control @error('jurisdiction') is-invalid @enderror"
                               value="{{ old('jurisdiction', $governingBody->jurisdiction) }}" maxlength="100">
                        @error('jurisdiction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                               value="{{ old('country', $governingBody->country) }}" maxlength="100">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $governingBody->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.governing-bodies.show', $governingBody) }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.governing-bodies.destroy', $governingBody) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this governing body?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
