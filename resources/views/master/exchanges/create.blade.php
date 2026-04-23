<x-app-layout>
    <x-slot name="title">New Exchange</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.exchanges.index') }}" class="text-muted small text-decoration-none">← Exchanges</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">New Exchange</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.exchanges.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" maxlength="20" placeholder="CME" style="text-transform:uppercase;">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="150" placeholder="CME Group">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Timezone</label>
                        <input type="text" name="timezone" class="form-control @error('timezone') is-invalid @enderror"
                               value="{{ old('timezone') }}" maxlength="50" placeholder="America/Chicago">
                        @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                               value="{{ old('country') }}" maxlength="100" placeholder="United States">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Exchange</button>
                    <a href="{{ route('master.exchanges.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
