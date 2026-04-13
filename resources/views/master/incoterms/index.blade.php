<x-app-layout><x-slot name="title">Incoterms</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Incoterms</span></div>
    <a href="{{ route('master.incoterms.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Code</th><th>Name</th><th>Active</th><th></th></tr></thead><tbody>
@forelse($incoterms as $i)
<tr><td><strong>{{ $i->code }}</strong></td><td>{{ $i->name }}</td><td>{{ $i->is_active ? '✓' : '—' }}</td>
<td class="text-end"><a href="{{ route('master.incoterms.edit', $i) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td></tr>
@empty<tr><td colspan="4" class="text-center text-muted py-3">No incoterms yet.</td></tr>@endforelse
</tbody></table></div>
@if($incoterms->hasPages())<div class="card-footer py-2">{{ $incoterms->links() }}</div>@endif</div>
</x-app-layout>
