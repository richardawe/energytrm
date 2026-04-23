<x-app-layout><x-slot name="title">{{ $settlementInstruction->si_number }}</x-slot>
<div class="mb-3">
    <a href="{{ route('master.settlement-instructions.index') }}" class="text-muted small text-decoration-none">← Settlement Instructions</a>
</div>
<div class="row g-3">
<div class="col-md-7">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>{{ $settlementInstruction->si_number }} — {{ $settlementInstruction->si_name }}</span>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted small">v{{ $settlementInstruction->version }}</span>
        @include('partials._status_badge', ['status' => $settlementInstruction->status])
        <a href="{{ route('master.settlement-instructions.edit', $settlementInstruction) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>
</div>
<div class="card-body">
<div class="row g-2">
    <div class="col-6"><div class="text-muted small">SI Number</div><div class="fw-semibold"><code>{{ $settlementInstruction->si_number }}</code></div></div>
    <div class="col-6"><div class="text-muted small">Party</div><div>
        @if($settlementInstruction->party)
            <a href="{{ route('master.parties.show', $settlementInstruction->party) }}" class="text-decoration-none">{{ $settlementInstruction->party->short_name }}</a>
        @else — @endif
    </div></div>
    <div class="col-6"><div class="text-muted small">Settler</div><div>{{ $settlementInstruction->settler ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Payment Method</div><div>{{ $settlementInstruction->payment_method ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Advice</div><div>{{ $settlementInstruction->advice ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Account Name</div><div>{{ $settlementInstruction->account_name ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Start Date</div><div>{{ $settlementInstruction->start_date?->format('d M Y') ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">End Date</div><div>{{ $settlementInstruction->end_date?->format('d M Y') ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">DVP</div><div>{{ $settlementInstruction->is_dvp ? 'Yes' : 'No' }}</div></div>
    @if($settlementInstruction->linkedSettlement)
    <div class="col-6"><div class="text-muted small">Linked SI</div><div><a href="{{ route('master.settlement-instructions.show', $settlementInstruction->linkedSettlement) }}" class="text-decoration-none">{{ $settlementInstruction->linkedSettlement->si_number }}</a></div></div>
    @endif
    @if($settlementInstruction->description)
    <div class="col-12"><div class="text-muted small">Description</div><div>{{ $settlementInstruction->description }}</div></div>
    @endif
    @if($settlementInstruction->author)
    <div class="col-12"><div class="text-muted small">Created by</div><div class="text-muted small">{{ $settlementInstruction->author->name }} on {{ $settlementInstruction->created_at->format('d M Y') }}</div></div>
    @endif
</div>
</div></div>
</div>
<div class="col-md-5">
<div class="card card-etrm"><div class="card-header">Actions</div>
<div class="card-body">
    <a href="{{ route('master.settlement-instructions.edit', $settlementInstruction) }}" class="btn btn-outline-secondary btn-sm w-100 mb-2">Edit Settlement Instruction</a>
    <form method="POST" action="{{ route('master.settlement-instructions.destroy', $settlementInstruction) }}" onsubmit="return confirm('Delete this settlement instruction?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm w-100">Delete</button>
    </form>
</div>
</div>
</div>
</div>
</x-app-layout>
