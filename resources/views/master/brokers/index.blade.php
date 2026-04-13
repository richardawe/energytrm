<x-app-layout><x-slot name="title">Brokers</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Brokers</span></div>
    <a href="{{ route('master.brokers.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Broker</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Name</th><th>Short Name</th><th>Type</th><th>LEI</th><th>Regulated</th><th>Status</th><th>Comm. Schedules</th><th></th></tr></thead><tbody>
@forelse($brokers as $b)
<tr>
    <td><a href="{{ route('master.brokers.show', $b) }}" class="text-decoration-none fw-semibold">{{ $b->name }}</a></td>
    <td>{{ $b->short_name ?? '—' }}</td><td>{{ $b->broker_type }}</td>
    <td><code>{{ $b->lei ?? '—' }}</code></td><td>{{ $b->is_regulated ? '✓' : '—' }}</td>
    <td>@include('partials._status_badge', ['status' => $b->status])</td>
    <td class="text-center">{{ $b->commissions_count }}</td>
    <td class="text-end"><a href="{{ route('master.brokers.edit', $b) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td>
</tr>
@empty<tr><td colspan="8" class="text-center text-muted py-3">No brokers yet.</td></tr>@endforelse
</tbody></table></div>
@if($brokers->hasPages())<div class="card-footer py-2">{{ $brokers->links() }}</div>@endif</div>
</x-app-layout>
