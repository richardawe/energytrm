<x-app-layout><x-slot name="title">Units of Measure</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Units of Measure</span></div>
    <a href="{{ route('master.uoms.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Code</th><th>Description</th><th>Conversion Factor</th><th>Base Unit</th><th>Active</th><th></th></tr></thead><tbody>
@forelse($uoms as $u)
<tr><td><strong>{{ $u->code }}</strong></td><td>{{ $u->description }}</td><td>{{ $u->conversion_factor }}</td><td>{{ $u->base_unit ?? '—' }}</td><td>{{ $u->is_active ? '✓' : '—' }}</td>
<td class="text-end"><a href="{{ route('master.uoms.edit', $u) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-3">No UOMs yet.</td></tr>@endforelse
</tbody></table></div>
@if($uoms->hasPages())<div class="card-footer py-2">{{ $uoms->links() }}</div>@endif</div>
</x-app-layout>
