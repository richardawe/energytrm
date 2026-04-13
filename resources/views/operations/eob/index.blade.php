<x-app-layout>
    <x-slot name="title">EoB Checklist</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('operations.dashboard') }}" class="text-muted small text-decoration-none">Operations</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">End-of-Business Checklist</span>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-muted small">Date:</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}"
                   onchange="this.form.submit()">
        </form>
    </div>

    @foreach($checklists as $cl)
    <div class="card card-etrm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $cl->businessUnit->short_name }}</span>
            @if($cl->signed_off)
                <span class="badge badge-authorized">
                    Signed off by {{ $cl->signedOffBy->name }} at {{ $cl->signed_off_at->format('H:i') }}
                </span>
            @else
                <span class="badge badge-pending">Pending sign-off</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                @php
                    $items = [
                        'all_trades_validated'      => 'All trades validated',
                        'all_invoices_issued'        => 'All invoices issued',
                        'all_settlements_confirmed'  => 'All settlements confirmed',
                        'all_nominations_matched'    => 'All nominations matched',
                    ];
                @endphp
                @foreach($items as $field => $label)
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        @if($cl->$field)
                            <span class="text-success fw-bold">&#10003;</span>
                        @else
                            <span class="text-danger fw-bold">&#10007;</span>
                        @endif
                        <span class="small">{{ $label }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                @if(!$cl->signed_off)
                    <form method="POST" action="{{ route('operations.eob.signOff', $cl) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('Sign off EoB checklist for {{ $cl->businessUnit->short_name }}?')">
                            Sign Off
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('operations.eob.reset', $cl) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning">Reset</button>
                    </form>
                @endif
                <form method="GET" action="{{ route('operations.eob.index') }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Refresh</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @if($checklists->isEmpty())
        <div class="text-center text-muted py-5">No internal business units found. Add BUs in Master Data first.</div>
    @endif
</x-app-layout>
