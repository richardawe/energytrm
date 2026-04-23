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

{{-- Subtabs: Addresses, Notes, Credit Ratings, Settlement Instructions --}}
<div class="col-12 mt-2" x-data="{ tab: 'addresses' }">
<div class="card card-etrm">
<div class="card-header p-0">
<ul class="nav nav-tabs card-header-tabs px-3">
    <li class="nav-item"><a class="nav-link" :class="{ active: tab==='addresses' }" @click.prevent="tab='addresses'" href="#">Addresses ({{ $party->addresses->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" :class="{ active: tab==='notes' }" @click.prevent="tab='notes'" href="#">Notes ({{ $party->notes->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" :class="{ active: tab==='credit-ratings' }" @click.prevent="tab='credit-ratings'" href="#">Credit Ratings ({{ $party->creditRatings->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" :class="{ active: tab==='si' }" @click.prevent="tab='si'" href="#">Settlement Instructions ({{ $party->settlementInstructions->count() }})</a></li>
</ul>
</div>
<div class="card-body p-0">
    <div x-show="tab==='addresses'">
        <div class="p-2 border-bottom d-flex justify-content-end">
            <a href="{{ route('master.parties.addresses.create', $party) }}" class="btn btn-sm btn-outline-primary">+ Add Address</a>
        </div>
        @if($party->addresses->isEmpty())<p class="text-muted p-3 mb-0 small">No addresses recorded.</p>
        @else
        <table class="table table-etrm mb-0"><thead><tr><th>Type</th><th>Default</th><th>Address</th><th>City</th><th>Country</th><th></th></tr></thead><tbody>
        @foreach($party->addresses as $addr)
        <tr>
            <td>{{ $addr->address_type }}</td>
            <td>{{ $addr->is_default ? '✓' : '' }}</td>
            <td>{{ $addr->address_line1 }}@if($addr->address_line2)<br><span class="text-muted small">{{ $addr->address_line2 }}</span>@endif</td>
            <td>{{ $addr->city }}</td><td>{{ $addr->country }}</td>
            <td class="text-end">
                <a href="{{ route('master.parties.addresses.edit', [$party, $addr]) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                <form method="POST" action="{{ route('master.parties.addresses.destroy', [$party, $addr]) }}" class="d-inline" onsubmit="return confirm('Delete this address?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-xs py-0 px-2" style="font-size:.75rem;">Del</button></form>
            </td>
        </tr>
        @endforeach
        </tbody></table>
        @endif
    </div>
    <div x-show="tab==='notes'" style="display:none">
        <div class="p-2 border-bottom d-flex justify-content-end">
            <a href="{{ route('master.parties.notes.create', $party) }}" class="btn btn-sm btn-outline-primary">+ Add Note</a>
        </div>
        @if($party->notes->isEmpty())<p class="text-muted p-3 mb-0 small">No notes.</p>
        @else
        <table class="table table-etrm mb-0"><thead><tr><th>Date</th><th>Type</th><th>Title</th><th></th></tr></thead><tbody>
        @foreach($party->notes as $n)
        <tr>
            <td>{{ $n->note_date->format('d M Y') }}</td><td>{{ $n->note_type ?? '—' }}</td><td>{{ $n->title }}</td>
            <td class="text-end">
                <a href="{{ route('master.parties.notes.edit', [$party, $n]) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                <form method="POST" action="{{ route('master.parties.notes.destroy', [$party, $n]) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-xs py-0 px-2" style="font-size:.75rem;">Del</button></form>
            </td>
        </tr>
        @endforeach
        </tbody></table>
        @endif
    </div>
    <div x-show="tab==='credit-ratings'" style="display:none">
        <div class="p-2 border-bottom d-flex justify-content-end">
            <a href="{{ route('master.parties.credit-ratings.create', $party) }}" class="btn btn-sm btn-outline-primary">+ Add Rating</a>
        </div>
        @if($party->creditRatings->isEmpty())<p class="text-muted p-3 mb-0 small">No credit ratings.</p>
        @else
        <table class="table table-etrm mb-0"><thead><tr><th>Source</th><th>Rating</th><th>Effective Date</th><th></th></tr></thead><tbody>
        @foreach($party->creditRatings as $cr)
        <tr>
            <td>{{ $cr->source }}</td><td><strong>{{ $cr->rating }}</strong></td>
            <td>{{ $cr->effective_date?->format('d M Y') ?? '—' }}</td>
            <td class="text-end">
                <a href="{{ route('master.parties.credit-ratings.edit', [$party, $cr]) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                <form method="POST" action="{{ route('master.parties.credit-ratings.destroy', [$party, $cr]) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-xs py-0 px-2" style="font-size:.75rem;">Del</button></form>
            </td>
        </tr>
        @endforeach
        </tbody></table>
        @endif
    </div>
    <div x-show="tab==='si'" style="display:none">
        @if($party->settlementInstructions->isEmpty())
            <p class="text-muted p-3 mb-0 small">No settlement instructions linked. <a href="{{ route('master.settlement-instructions.create') }}">Create one</a>.</p>
        @else
        <table class="table table-etrm mb-0"><thead><tr><th>SI Number</th><th>Name</th><th>Status</th><th>DVP</th></tr></thead><tbody>
        @foreach($party->settlementInstructions as $si)
        <tr>
            <td><a href="{{ route('master.settlement-instructions.show', $si) }}" class="text-decoration-none">{{ $si->si_number }}</a></td>
            <td>{{ $si->si_name }}</td>
            <td>@include('partials._status_badge', ['status' => $si->status])</td>
            <td>{{ $si->is_dvp ? '✓' : '' }}</td>
        </tr>
        @endforeach
        </tbody></table>
        @endif
    </div>
</div>
</div>
</div>
</x-app-layout>
