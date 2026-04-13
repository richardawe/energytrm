<x-app-layout>
    <x-slot name="title">Currencies</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Currencies</span>
        </div>
        <a href="{{ route('master.currencies.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Currency</a>
    </div>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th><th>Name</th><th>Symbol</th><th>FX Rate (to USD)</th><th>Active</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $c)
                    <tr>
                        <td><strong>{{ $c->code }}</strong></td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->symbol }}</td>
                        <td>{{ number_format($c->fx_rate_to_usd, 4) }}</td>
                        <td>{{ $c->is_active ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.currencies.edit', $c) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No currencies defined yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($currencies->hasPages())
        <div class="card-footer py-2">{{ $currencies->links() }}</div>
        @endif
    </div>
</x-app-layout>
