<x-app-layout>
    <x-slot name="title">Edit User</x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.users.index') }}" class="text-muted small text-decoration-none">← Users</a>
    </div>

    <div class="card card-etrm" style="max-width:620px;">
        <div class="card-header">Edit User — {{ $user->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PATCH')

                <div x-data="{ tab: 'core' }">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item"><a class="nav-link" :class="{ active: tab==='core' }" @click.prevent="tab='core'" href="#">Core</a></li>
                        <li class="nav-item"><a class="nav-link" :class="{ active: tab==='personnel' }" @click.prevent="tab='personnel'" href="#">Personnel Details</a></li>
                        <li class="nav-item"><a class="nav-link" :class="{ active: tab==='assignments' }" @click.prevent="tab='assignments'" href="#">Assignments</a></li>
                    </ul>

                    {{-- Core Tab --}}
                    <div x-show="tab==='core'">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror">
                                    <option value="admin"       {{ old('role', $user->role) === 'admin'       ? 'selected' : '' }}>Admin</option>
                                    <option value="trader"      {{ old('role', $user->role) === 'trader'      ? 'selected' : '' }}>Trader</option>
                                    <option value="back_office" {{ old('role', $user->role) === 'back_office' ? 'selected' : '' }}>Back Office</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-1">
                                <p class="text-muted small mb-2">Leave password fields blank to keep the current password.</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    {{-- Personnel Tab --}}
                    <div x-show="tab==='personnel'" style="display:none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">User Type</label>
                                <select name="user_type" class="form-select @error('user_type') is-invalid @enderror">
                                    <option value="">— Select —</option>
                                    @foreach(['Internal','External','Licensed'] as $t)
                                    <option value="{{ $t }}" {{ old('user_type', $user->user_type ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">License Type</label>
                                <select name="license_type" class="form-select @error('license_type') is-invalid @enderror">
                                    <option value="">— Select —</option>
                                    @foreach(['Full Access','Server','Read Only'] as $l)
                                    <option value="{{ $l }}" {{ old('license_type', $user->license_type ?? '') === $l ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                                @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Short Ref Name</label>
                                <input type="text" name="short_ref_name" class="form-control @error('short_ref_name') is-invalid @enderror" value="{{ old('short_ref_name', $user->short_ref_name ?? '') }}" maxlength="32">
                                @error('short_ref_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Short Alias Name</label>
                                <input type="text" name="short_alias_name" class="form-control @error('short_alias_name') is-invalid @enderror" value="{{ old('short_alias_name', $user->short_alias_name ?? '') }}" maxlength="50">
                                @error('short_alias_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $user->employee_id ?? '') }}" maxlength="50">
                                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $user->title ?? '') }}" maxlength="100">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}" maxlength="50">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address ?? '') }}" maxlength="255">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $user->city ?? '') }}" maxlength="100">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $user->state ?? '') }}" maxlength="100">
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $user->country ?? '') }}" maxlength="100">
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="">— Select —</option>
                                    @foreach(['Authorized','Auth Pending','Do Not Use'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $user->status ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="password_never_expires" value="1" id="password_never_expires"
                                           {{ old('password_never_expires', $user->password_never_expires ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_never_expires">Password Never Expires</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Assignments Tab --}}
                    <div x-show="tab==='assignments'" style="display:none">
                        <h6>Business Units</h6>
                        <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;padding:.5rem" class="mb-3">
                            @forelse($parties as $p)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="business_units[]" value="{{ $p->id }}" id="bu_{{ $p->id }}"
                                       {{ in_array($p->id, old('business_units', $assignedBUs ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="bu_{{ $p->id }}">{{ $p->short_name }} — {{ $p->long_name }}</label>
                            </div>
                            @empty
                            <p class="text-muted small mb-0">No business units available.</p>
                            @endforelse
                        </div>

                        <h6>Portfolios</h6>
                        <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;padding:.5rem" class="mb-3">
                            @forelse($portfolios as $portfolio)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="portfolios[]" value="{{ $portfolio->id }}" id="pf_{{ $portfolio->id }}"
                                       {{ in_array($portfolio->id, old('portfolios', $assignedPortfolios ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="pf_{{ $portfolio->id }}">{{ $portfolio->name }}</label>
                            </div>
                            @empty
                            <p class="text-muted small mb-0">No portfolios available.</p>
                            @endforelse
                        </div>

                        <h6>Security Groups</h6>
                        <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.375rem;padding:.5rem" class="mb-3">
                            @forelse($securityGroups as $sg)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="security_groups[]" value="{{ $sg->id }}" id="sg_{{ $sg->id }}"
                                       {{ in_array($sg->id, old('security_groups', $assignedSGs ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sg_{{ $sg->id }}">{{ $sg->name }}</label>
                            </div>
                            @empty
                            <p class="text-muted small mb-0">No security groups available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
