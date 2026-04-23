<x-app-layout>
    <x-slot name="title">Edit Exchange</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.exchanges.show', $exchange) }}" class="text-muted small text-decoration-none">← {{ $exchange->name }}</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Exchange — {{ $exchange->code }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.exchanges.update', $exchange) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $exchange->code) }}" maxlength="20" style="text-transform:uppercase;">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $exchange->name) }}" maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Timezone</label>
                        <input type="text" name="timezone" class="form-control @error('timezone') is-invalid @enderror"
                               value="{{ old('timezone', $exchange->timezone) }}" maxlength="50">
                        @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                               value="{{ old('country', $exchange->country) }}" maxlength="100">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $exchange->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.exchanges.show', $exchange) }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.exchanges.destroy', $exchange) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this exchange?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
