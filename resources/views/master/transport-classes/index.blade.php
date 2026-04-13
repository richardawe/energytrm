<x-app-layout><x-slot name="title">Transport Classes</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Transport Classes</span></div>
    <a href="{{ route('master.transport-classes.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Name</th><th>Description</th><th>Active</th><th></th></tr></thead><tbody>
@forelse($transportClasses as $tc)
<tr><td>{{ $tc->name }}</td><td class="text-muted">{{ $tc->description }}</td><td>{{ $tc->is_active ? '✓' : '—' }}</td>
<td class="text-end"><a href="{{ route('master.transport-classes.edit', $tc) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td></tr>
@empty<tr><td colspan="4" class="text-center text-muted py-3">No transport classes yet.</td></tr>@endforelse
</tbody></table></div>
@if($transportClasses->hasPages())<div class="card-footer py-2">{{ $transportClasses->links() }}</div>@endif</div>
</x-app-layout>
