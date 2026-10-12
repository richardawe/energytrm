<x-app-layout>
<x-slot name="title">{{ $index->index_name }}</x-slot>
<div class="mb-3"><a href="{{ route('master.indices.index') }}" class="text-muted small text-decoration-none">← Indices</a></div>

<div class="row g-3">
    {{-- Left column: core details + curve config --}}
    <div class="col-md-5">
        <div class="card card-etrm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ $index->index_name }}</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small">v{{ $index->version }}</span>
                    @include('partials._status_badge', ['status' => $index->rec_status])
                    <a href="{{ route('master.indices.edit', $index) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2" style="font-size:.9rem;">
                    @if($index->label)
                    <div class="col-6"><div class="text-muted small">Label</div><div>{{ $index->label }}</div></div>
                    @endif
                    <div class="col-6"><div class="text-muted small">Market</div><div>{{ $index->market ?? '—' }}</div></div>
                    <div class="col-6"><div class="text-muted small">Index Group</div><div>{{ $index->index_group ?? '—' }}</div></div>
                    @if($index->index_subgroup)
                    <div class="col-6"><div class="text-muted small">Sub-Group</div><div>{{ $index->index_subgroup }}</div></div>
                    @endif
                    <div class="col-6"><div class="text-muted small">Format</div><div>{{ $index->format }}</div></div>
                    <div class="col-6"><div class="text-muted small">Class</div><div>{{ $index->class ?? '—' }}</div></div>
                    @if($index->index_type)
                    <div class="col-6"><div class="text-muted small">Index Type</div><div>{{ $index->index_type }}</div></div>
                    @endif
                    <div class="col-6"><div class="text-muted small">Currency</div><div>{{ $index->baseCurrency?->code ?? '—' }}</div></div>
                    <div class="col-6"><div class="text-muted small">UOM</div><div>{{ $index->uom?->code ?? '—' }}</div></div>
                    <div class="col-6"><div class="text-muted small">Status</div><div>{{ $index->status }}</div></div>
                    @if($index->version_status)
                    <div class="col-6"><div class="text-muted small">Version Status</div><div>{{ $index->version_status }}</div></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Curve Configuration --}}
        @if($index->delivery_unit || $index->interpolation || $index->projection_method || $index->reference_source || $index->discount_index_id || $index->holiday_schedule)
        <div class="card card-etrm mb-3">
            <div class="card-header">Curve Configuration</div>
            <div class="card-body">
                <div class="row g-2" style="font-size:.9rem;">
                    @if($index->delivery_unit)
                    <div class="col-6"><div class="text-muted small">Delivery Unit</div><div>{{ $index->delivery_unit }}</div></div>
                    @endif
                    @if($index->date_sequence)
                    <div class="col-6"><div class="text-muted small">Date Sequence</div><div>{{ $index->date_sequence }}</div></div>
                    @endif
                    @if($index->payment_convention)
                    <div class="col-6"><div class="text-muted small">Payment Convention</div><div>{{ $index->payment_convention }}</div></div>
                    @endif
                    @if($index->coverage_end_date)
                    <div class="col-6"><div class="text-muted small">Coverage End</div><div>{{ $index->coverage_end_date->format('d M Y') }}</div></div>
                    @endif
                    @if($index->interpolation)
                    <div class="col-6"><div class="text-muted small">Interpolation</div><div>{{ $index->interpolation }}</div></div>
                    @endif
                    @if($index->projection_method)
                    <div class="col-6"><div class="text-muted small">Projection Method</div><div>{{ $index->projection_method }}</div></div>
                    @endif
                    @if($index->reference_source)
                    <div class="col-6"><div class="text-muted small">Reference Source</div><div>{{ $index->reference_source }}</div></div>
                    @endif
                    @if($index->day_start_time)
                    <div class="col-6"><div class="text-muted small">Day Start Time</div><div>{{ $index->day_start_time }}</div></div>
                    @endif
                    @if($index->holiday_schedule)
                    <div class="col-6"><div class="text-muted small">Holiday Schedule</div><div>{{ $index->holiday_schedule }}</div></div>
                    @endif
                    @if($index->discountIndex)
                    <div class="col-12"><div class="text-muted small">Discount Index</div><div>{{ $index->discountIndex->index_name }}</div></div>
                    @endif
                    <div class="col-6"><div class="text-muted small">Inheritance</div><div>{{ $index->inheritance ? 'Yes' : 'No' }}</div></div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right column: price grid --}}
    <div class="col-md-7">
        <div class="card card-etrm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Price Grid Points ({{ $index->gridPoints->count() }})</span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPriceModal">+ Add Price</button>
            </div>
            <div class="card-body p-0" style="max-height:500px;overflow-y:auto;">
                <table class="table table-etrm mb-0">
                    <thead><tr><th>Date</th><th class="text-end">Price</th><th>Entered By</th></tr></thead>
                    <tbody>
                    @forelse($index->gridPoints->sortByDesc('price_date') as $gp)
                    <tr>
                        <td>{{ $gp->price_date->format('d M Y') }}</td>
                        <td class="text-end fw-semibold">{{ number_format($gp->price, 4) }}</td>
                        <td class="text-muted">{{ $gp->enteredBy?->display_name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No price data yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Price Modal --}}
<div class="modal fade" id="addPriceModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Add Grid Point</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('master.indices.update', $index) }}">@csrf @method('PUT')
                    <input type="hidden" name="add_grid_point" value="1">
                    <input type="hidden" name="index_name" value="{{ $index->index_name }}">
                    <input type="hidden" name="market" value="{{ $index->market }}">
                    <input type="hidden" name="index_group" value="{{ $index->index_group }}">
                    <input type="hidden" name="format" value="{{ $index->format }}">
                    <input type="hidden" name="class" value="{{ $index->class }}">
                    <input type="hidden" name="base_currency_id" value="{{ $index->base_currency_id }}">
                    <input type="hidden" name="uom_id" value="{{ $index->uom_id }}">
                    <input type="hidden" name="status" value="{{ $index->status }}">
                    <input type="hidden" name="rec_status" value="{{ $index->rec_status }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date *</label>
                        <input type="date" name="grid_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Price *</label>
                        <input type="number" name="grid_price" class="form-control" step="0.000001" required placeholder="e.g. 85.42">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Add Price</button>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
