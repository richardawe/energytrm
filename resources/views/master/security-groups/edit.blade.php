<x-app-layout>
    <x-slot name="title">Edit Security Group</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.security-groups.index') }}" class="text-muted small text-decoration-none">← Security Groups</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">Edit Security Group — {{ $securityGroup->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.security-groups.update', $securityGroup) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $securityGroup->name) }}" maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $securityGroup->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $securityGroup->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
                    <a href="{{ route('master.security-groups.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <form method="POST" action="{{ route('master.security-groups.destroy', $securityGroup) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this security group?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
