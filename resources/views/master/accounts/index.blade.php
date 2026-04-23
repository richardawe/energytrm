<x-app-layout><x-slot name="title">Accounts</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Accounts</span></div>
    <a href="{{ route('master.accounts.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Account</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0">
<thead><tr>
    <th>Account Number</th>
    <th>Name</th>
    <th>Type</th>
    <th>Holding Party</th>
    <th>Currency</th>
    <th>Status</th>
    <th></th>
</tr></thead>
<tbody>
@forelse($accounts as $a)
<tr>
    <td><a href="{{ route('master.accounts.show', $a) }}" class="text-decoration-none fw-semibold"><code>{{ $a->account_number }}</code></a></td>
    <td>{{ $a->account_name }}</td>
    <td>{{ $a->account_type }}</td>
    <td>{{ $a->holdingParty?->short_name ?? '—' }}</td>
    <td>{{ $a->currency?->code ?? '—' }}</td>
    <td>@include('partials._status_badge', ['status' => $a->status])</td>
    <td class="text-end">
        <a href="{{ route('master.accounts.edit', $a) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
    </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-3">No accounts yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
@if($accounts->hasPages())<div class="card-footer py-2">{{ $accounts->links() }}</div>@endif
</div>
</x-app-layout>
