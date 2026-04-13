<x-app-layout>
    <x-slot name="title">{{ $index->index_name }} — Prices</x-slot>

    <div class="mb-3">
        <a href="{{ route('financials.market-prices.index') }}" class="text-muted small text-decoration-none">← Market Prices</a>
    </div>

    <div class="row g-3">
        {{-- Entry form --}}
        <div class="col-lg-4">
            <div class="card card-etrm mb-3">
                <div class="card-header">{{ $index->index_name }}</div>
                <div class="card-body" style="font-size:.875rem;">
                    <div class="row g-1 mb-3">
                        <div class="col-5 text-muted">Market</div><div class="col-7">{{ $index->market }}</div>
                        <div class="col-5 text-muted">Class</div><div class="col-7">{{ $index->class }}</div>
                        <div class="col-5 text-muted">Currency</div><div class="col-7">{{ $index->baseCurrency->code }}</div>
                        @if($index->latestPrice)
                        <div class="col-5 text-muted">Latest</div>
                        <div class="col-7 fw-bold">{{ number_format($index->latestPrice->price, 4) }}
                            <span class="text-muted fw-normal small">{{ $index->latestPrice->price_date->format('d-M-Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-etrm">
                <div class="card-header">Add / Update Price</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('financials.market-prices.store', $index) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="price_date" class="form-control @error('price_date') is-invalid @enderror"
                                   value="{{ old('price_date', date('Y-m-d')) }}">
                            @error('price_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $index->baseCurrency->code }}</span>
                                <input type="number" name="price" step="0.000001" min="0"
                                       class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price') }}" placeholder="0.000000">
                            </div>
                            <div class="form-text">Existing price for this date will be overwritten.</div>
                            @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100"
                                style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">
                            Save Price
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Price history --}}
        <div class="col-lg-8">
            <div class="card card-etrm">
                <div class="card-header">Price History</div>
                <div class="card-body p-0">
                    <table class="table table-etrm table-hover mb-0" style="font-size:.875rem;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Price ({{ $index->baseCurrency->code }})</th>
                                <th>Entered By</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($points as $pt)
                            <tr>
                                <td class="fw-semibold">{{ $pt->price_date->format('d-M-Y') }}</td>
                                <td class="text-end">{{ number_format($pt->price, 4) }}</td>
                                <td>{{ $pt->enteredBy?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $pt->updated_at->format('d-M-Y H:i') }}</td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('financials.market-prices.destroy', $pt) }}"
                                          onsubmit="return confirm('Delete this price point?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-2"
                                                style="font-size:.75rem;">Del</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No prices entered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($points->hasPages())
                <div class="card-footer py-2">{{ $points->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
