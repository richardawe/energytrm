<x-app-layout><x-slot name="title">{{ $party->short_name }}</x-slot>
<div class="mb-3"><a href="{{ route('master.parties.index') }}" class="text-muted small text-decoration-none">← Parties</a></div>
<div class="row g-3">
<div class="col-md-8">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>{{ $party->long_name }}</span>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted small">v{{ $party->version }}</span>
        @include('partials._status_badge', ['status' => $party->status])
        <a href="{{ route('master.parties.edit', $party) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>
</div>
<div class="card-body">
<div class="row g-2">
    <div class="col-6"><div class="text-muted small">Short Name</div><div class="fw-semibold">{{ $party->short_name }}</div></div>
    <div class="col-6"><div class="text-muted small">Type</div><div><span class="badge text-bg-secondary">{{ $party->party_type }}</span></div></div>
    <div class="col-6"><div class="text-muted small">Internal / External</div><div>{{ $party->internal_external }}</div></div>
    <div class="col-6"><div class="text-muted small">Parent</div><div>{{ $party->parent?->short_name ?? '—' }}</div></div>
    @if($party->lei)<div class="col-6"><div class="text-muted small">LEI</div><div><code>{{ $party->lei }}</code></div></div>@endif
    @if($party->bic_swift)<div class="col-6"><div class="text-muted small">BIC/SWIFT</div><div><code>{{ $party->bic_swift }}</code></div></div>@endif
    @if($party->credit_limit)<div class="col-6"><div class="text-muted small">Credit Limit</div><div>{{ number_format($party->credit_limit,2) }} {{ $party->creditLimitCurrency?->code }}</div></div>@endif
    @if($party->kyc_status)<div class="col-6"><div class="text-muted small">KYC Status</div><div>{{ $party->kyc_status }} @if($party->kyc_review_date)<span class="text-muted">(due {{ $party->kyc_review_date->format('d M Y') }})</span>@endif</div></div>@endif
    @if($party->regulatory_class)<div class="col-6"><div class="text-muted small">Regulatory Class</div><div>{{ $party->regulatory_class }}</div></div>@endif
</div>
</div></div>

@if($party->portfolios->count())
<div class="card card-etrm mt-3"><div class="card-header">Portfolios ({{ $party->portfolios->count() }})</div>
<div class="card-body p-0"><table class="table table-etrm mb-0"><thead><tr><th>Portfolio</th><th>Restricted</th><th>Status</th></tr></thead><tbody>
@foreach($party->portfolios as $pf)<tr><td>{{ $pf->name }}</td><td>{{ $pf->is_restricted ? 'Yes' : 'No' }}</td><td>@include('partials._status_badge', ['status' => $pf->status])</td></tr>@endforeach
</tbody></table></div></div>
@endif
</div>

@if($party->children->count())
<div class="col-md-4"><div class="card card-etrm"><div class="card-header">Children ({{ $party->children->count() }})</div>
<div class="card-body p-0"><table class="table table-etrm mb-0"><thead><tr><th>Short Name</th><th>Type</th></tr></thead><tbody>
@foreach($party->children as $child)<tr><td><a href="{{ route('master.parties.show', $child) }}" class="text-decoration-none">{{ $child->short_name }}</a></td><td><span class="badge text-bg-secondary">{{ $child->party_type }}</span></td></tr>@endforeach
</tbody></table></div></div></div>
@endif
</div>
</x-app-layout>
