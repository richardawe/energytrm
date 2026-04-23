<x-app-layout>
    <x-slot name="title">User — {{ $user->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><a href="{{ route('admin.users.index') }}" class="text-muted small text-decoration-none">← User Management</a></div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Edit</a>
    </div>

    {{-- Core Details --}}
    <div class="card card-etrm mb-3" style="max-width:700px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $user->name }}</span>
            @php
                $badge = match($user->role) {
                    'admin'       => 'danger',
                    'trader'      => 'primary',
                    'back_office' => 'secondary',
                    default       => 'light',
                };
                $label = match($user->role) {
                    'admin'       => 'Admin',
                    'trader'      => 'Trader',
                    'back_office' => 'Back Office',
                    default       => $user->role,
                };
            @endphp
            <span class="badge bg-{{ $badge }}">{{ $label }}</span>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4 text-muted">Status</dt>
                <dd class="col-sm-8">
                    @if($user->status ?? null)
                        @include('partials._status_badge', ['status' => $user->status])
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">User Type</dt>
                <dd class="col-sm-8">{{ $user->user_type ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Personnel ID</dt>
                <dd class="col-sm-8"><code>{{ $user->personnel_id ?? '—' }}</code></dd>

                <dt class="col-sm-4 text-muted">Employee ID</dt>
                <dd class="col-sm-8"><code>{{ $user->employee_id ?? '—' }}</code></dd>

                <dt class="col-sm-4 text-muted">Member Since</dt>
                <dd class="col-sm-8 text-muted small">{{ $user->created_at->format('d M Y') }}</dd>
            </dl>
        </div>
    </div>

    {{-- Personnel Details --}}
    <div class="card card-etrm mb-3" style="max-width:700px;">
        <div class="card-header">Personnel Details</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Title</dt>
                <dd class="col-sm-8">{{ $user->title ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Phone</dt>
                <dd class="col-sm-8">{{ $user->phone ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Short Ref Name</dt>
                <dd class="col-sm-8"><code>{{ $user->short_ref_name ?? '—' }}</code></dd>

                <dt class="col-sm-4 text-muted">Short Alias Name</dt>
                <dd class="col-sm-8"><code>{{ $user->short_alias_name ?? '—' }}</code></dd>

                <dt class="col-sm-4 text-muted">Address</dt>
                <dd class="col-sm-8">
                    @if($user->address || $user->city || $user->state || $user->country)
                        {{ implode(', ', array_filter([$user->address, $user->city, $user->state, $user->country])) }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">License Type</dt>
                <dd class="col-sm-8">{{ $user->license_type ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Password Never Expires</dt>
                <dd class="col-sm-8">{{ ($user->password_never_expires ?? false) ? 'Yes' : 'No' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Assigned Business Units --}}
    @if($user->businessUnits->isNotEmpty())
    <div class="card card-etrm mb-3" style="max-width:700px;">
        <div class="card-header">Assigned Business Units</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead><tr><th>Short Name</th><th>Long Name</th></tr></thead>
                <tbody>
                    @foreach($user->businessUnits as $bu)
                    <tr>
                        <td>{{ $bu->short_name }}</td>
                        <td>{{ $bu->long_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Assigned Portfolios --}}
    @if($user->portfolios->isNotEmpty())
    <div class="card card-etrm mb-3" style="max-width:700px;">
        <div class="card-header">Assigned Portfolios</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead><tr><th>Name</th><th>Description</th></tr></thead>
                <tbody>
                    @foreach($user->portfolios as $portfolio)
                    <tr>
                        <td>{{ $portfolio->name }}</td>
                        <td class="text-muted">{{ $portfolio->description ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Assigned Security Groups --}}
    @if($user->securityGroups->isNotEmpty())
    <div class="card card-etrm mb-3" style="max-width:700px;">
        <div class="card-header">Assigned Security Groups</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead><tr><th>Name</th><th>Description</th></tr></thead>
                <tbody>
                    @foreach($user->securityGroups as $sg)
                    <tr>
                        <td>{{ $sg->name }}</td>
                        <td class="text-muted">{{ $sg->description ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</x-app-layout>
