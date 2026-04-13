<x-app-layout><x-slot name="title">Agreements</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Agreements</span></div>
    <a href="{{ route('master.agreements.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Agreement</th><th>Internal Party</th><th>Counterparty</th><th>Payment Terms</th><th>Effective</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($agreements as $a)
<tr>
    <td>{{ $a->name }}</td><td>{{ $a->internalParty?->short_name ?? '—' }}</td><td>{{ $a->counterparty?->short_name ?? '—' }}</td>
    <td>{{ $a->paymentTerms?->name ?? '—' }}</td><td>{{ $a->effective_date?->format('d M Y') ?? '—' }}</td>
    <td>@include('partials._status_badge', ['status' => $a->status])</td>
    <td class="text-end"><a href="{{ route('master.agreements.edit', $a) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted py-3">No agreements yet.</td></tr>@endforelse
</tbody></table></div>
@if($agreements->hasPages())<div class="card-footer py-2">{{ $agreements->links() }}</div>@endif</div>
</x-app-layout>
