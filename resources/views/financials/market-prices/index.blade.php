<x-app-layout>
    <x-slot name="title">Market Prices</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('financials.dashboard') }}" class="text-muted small text-decoration-none">Financials</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Market Prices</span>
        </div>
    </div>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.875rem;">
                <thead>
                    <tr>
                        <th>Index</th>
                        <th>Market</th>
                        <th>Class</th>
                        <th>Currency</th>
                        <th class="text-end">Latest Price</th>
                        <th>As of Date</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indices as $idx)
                    <tr>
                        <td class="fw-semibold">{{ $idx->index_name }}</td>
                        <td>{{ $idx->market }}</td>
                        <td>{{ $idx->class }}</td>
                        <td>{{ $idx->baseCurrency->code }}</td>
                        <td class="text-end fw-semibold">
                            {{ $idx->latestPrice ? number_format($idx->latestPrice->price, 4) : '—' }}
                        </td>
                        <td>{{ $idx->latestPrice?->price_date?->format('d-M-Y') ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-authorized">{{ $idx->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('financials.market-prices.show', $idx) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">
                                Enter Price
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No indices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
