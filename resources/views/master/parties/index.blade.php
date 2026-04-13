<x-app-layout><x-slot name="title">Parties</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Parties</span></div>
    <a href="{{ route('master.parties.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Party</a>
</div>

<div class="filter-bar d-flex gap-3 flex-wrap align-items-end">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
        <div>
            <label class="form-label fw-semibold mb-1 small">Type</label>
            <select name="type" class="form-select form-select-sm" style="min-width:130px;">
                <option value="">All Types</option>
                <option value="Group" {{ request('type')=='Group' ? 'selected' : '' }}>Party Group</option>
                <option value="LE" {{ request('type')=='LE' ? 'selected' : '' }}>Legal Entity</option>
                <option value="BU" {{ request('type')=='BU' ? 'selected' : '' }}>Business Unit</option>
            </select>
        </div>
        <div>
            <label class="form-label fw-semibold mb-1 small">Internal / External</label>
            <select name="ie" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="Internal" {{ request('ie')=='Internal' ? 'selected' : '' }}>Internal</option>
                <option value="External" {{ request('ie')=='External' ? 'selected' : '' }}>External</option>
            </select>
        </div>
        <div>
            <label class="form-label fw-semibold mb-1 small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="Authorized" {{ request('status')=='Authorized' ? 'selected' : '' }}>Authorized</option>
                <option value="Auth Pending" {{ request('status')=='Auth Pending' ? 'selected' : '' }}>Auth Pending</option>
                <option value="Do Not Use" {{ request('status')=='Do Not Use' ? 'selected' : '' }}>Do Not Use</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('master.parties.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
    </form>
</div>

<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Short Name</th><th>Long Name</th><th>Type</th><th>Int/Ext</th><th>Parent</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($parties as $p)
<tr>
    <td><a href="{{ route('master.parties.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->short_name }}</a></td>
    <td>{{ $p->long_name }}</td>
    <td><span class="badge text-bg-secondary">{{ $p->party_type }}</span></td>
    <td>{{ $p->internal_external }}</td>
    <td class="text-muted">{{ $p->parent?->short_name ?? '—' }}</td>
    <td>@include('partials._status_badge', ['status' => $p->status])</td>
    <td class="text-end"><a href="{{ route('master.parties.edit', $p) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted py-3">No parties found.</td></tr>@endforelse
</tbody></table></div>
@if($parties->hasPages())<div class="card-footer py-2">{{ $parties->links() }}</div>@endif</div>
</x-app-layout>
