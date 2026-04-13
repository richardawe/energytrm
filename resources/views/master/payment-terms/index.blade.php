<x-app-layout><x-slot name="title">Payment Terms</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Payment Terms</span></div>
    <a href="{{ route('master.payment-terms.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Name</th><th>Days Net</th><th>Description</th><th>Active</th><th></th></tr></thead><tbody>
@forelse($paymentTerms as $pt)
<tr><td>{{ $pt->name }}</td><td>{{ $pt->days_net }}</td><td class="text-muted">{{ $pt->description ?? '—' }}</td><td>{{ $pt->is_active ? '✓' : '—' }}</td>
<td class="text-end"><a href="{{ route('master.payment-terms.edit', $pt) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-3">No payment terms yet.</td></tr>@endforelse
</tbody></table></div>
@if($paymentTerms->hasPages())<div class="card-footer py-2">{{ $paymentTerms->links() }}</div>@endif</div>
</x-app-layout>
