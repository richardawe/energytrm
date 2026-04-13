<x-app-layout>
    <x-slot name="title">Nominations</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('operations.dashboard') }}" class="text-muted small text-decoration-none">Operations</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Nominations</span>
        </div>
        <a href="{{ route('operations.nominations.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Nomination</a>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['Pending','Confirmed','Matched','Unmatched'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="gas_day" class="form-control form-control-sm" value="{{ request('gas_day') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('operations.nominations.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Nom No</th>
                        <th>Trade</th>
                        <th>Gas Day</th>
                        <th>Pipeline</th>
                        <th>Delivery Point</th>
                        <th class="text-end">Nominated</th>
                        <th class="text-end">Confirmed</th>
                        <th>UOM</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nominations as $n)
                    <tr>
                        <td class="fw-semibold">{{ $n->nomination_number }}</td>
                        <td><a href="{{ route('trades.show', $n->trade) }}" class="text-decoration-none">{{ $n->trade->deal_number }}</a></td>
                        <td>{{ $n->gas_day->format('d-M-Y') }}</td>
                        <td>{{ $n->pipeline_operator ?: '—' }}</td>
                        <td>{{ $n->delivery_point ?: '—' }}</td>
                        <td class="text-end">{{ number_format($n->nominated_volume, 2) }}</td>
                        <td class="text-end">{{ $n->confirmed_volume ? number_format($n->confirmed_volume, 2) : '—' }}</td>
                        <td>{{ $n->uom->code }}</td>
                        <td class="text-center">
                            @php
                                $cls = match($n->nomination_status) {
                                    'Pending'   => 'badge-pending',
                                    'Confirmed' => 'badge-validated',
                                    'Matched'   => 'badge-authorized',
                                    'Unmatched' => 'badge-do-not-use',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ $n->nomination_status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('operations.nominations.edit', $n) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No nominations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($nominations->hasPages())
        <div class="card-footer py-2">{{ $nominations->links() }}</div>
        @endif
    </div>
</x-app-layout>
