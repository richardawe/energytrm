<x-app-layout>
    <x-slot name="title">{{ $pipeline->code }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('master.pipelines.index') }}" class="text-muted small text-decoration-none">← Pipelines</a>
        @can('update', $pipeline)
        <a href="{{ route('master.pipelines.edit', $pipeline) }}" class="btn btn-sm btn-outline-secondary">Edit Pipeline</a>
        @endcan
    </div>

    <div class="card card-etrm mb-3">
        <div class="card-header fw-semibold">{{ $pipeline->code }} — {{ $pipeline->name }}</div>
        <div class="card-body" style="font-size:.9rem;">
            <div class="row g-2">
                <div class="col-md-2 text-muted">Commodity</div>
                <div class="col-md-2">{{ $pipeline->commodity_type }}</div>
                <div class="col-md-2 text-muted">Operator</div>
                <div class="col-md-2">{{ $pipeline->operator ?: '—' }}</div>
                <div class="col-md-2 text-muted">Country</div>
                <div class="col-md-2">{{ $pipeline->country ?: '—' }}</div>
                <div class="col-md-2 text-muted">Status</div>
                <div class="col-md-2">
                    <span class="badge {{ $pipeline->status === 'Authorized' ? 'badge-authorized' : 'bg-danger' }}">{{ $pipeline->status }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Zones --}}
    @foreach($pipeline->zones as $zone)
    <div class="card card-etrm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">Zone: {{ $zone->zone_code }} — {{ $zone->zone_name }}</span>
            <span class="badge {{ $zone->status === 'Authorized' ? 'badge-authorized' : 'bg-danger' }} ms-2">{{ $zone->status }}</span>
        </div>
        @if($zone->locations->isNotEmpty())
        <div class="card-body p-0">
            <table class="table table-etrm mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Code</th><th>Name</th><th>Type</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zone->locations as $loc)
                    <tr>
                        <td class="fw-semibold">{{ $loc->location_code }}</td>
                        <td>{{ $loc->location_name }}</td>
                        <td>{{ $loc->location_type }}</td>
                        <td><span class="badge {{ $loc->status === 'Authorized' ? 'badge-authorized' : 'bg-danger' }}">{{ $loc->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @can('create', App\Models\PipelineLocation::class)
        <div class="card-footer py-2">
            <form method="POST" action="{{ route('master.pipelines.locations.store', [$pipeline, $zone]) }}"
                  class="d-flex gap-2 align-items-end" style="font-size:.85rem;">
                @csrf
                <div>
                    <label class="form-label mb-1 small">Code</label>
                    <input type="text" name="location_code" class="form-control form-control-sm" style="width:100px;" placeholder="LOC-01">
                </div>
                <div>
                    <label class="form-label mb-1 small">Name</label>
                    <input type="text" name="location_name" class="form-control form-control-sm" style="width:180px;">
                </div>
                <div>
                    <label class="form-label mb-1 small">Type</label>
                    <select name="location_type" class="form-select form-select-sm" style="width:110px;">
                        <option value="Both">Both</option>
                        <option value="Receipt">Receipt</option>
                        <option value="Delivery">Delivery</option>
                    </select>
                </div>
                <input type="hidden" name="status" value="Authorized">
                <button type="submit" class="btn btn-sm btn-outline-secondary mb-0">+ Location</button>
            </form>
        </div>
        @endcan
    </div>
    @endforeach

    @can('create', App\Models\PipelineZone::class)
    <div class="card card-etrm mb-3">
        <div class="card-header py-2 fw-semibold">Add Zone</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.pipelines.zones.store', $pipeline) }}"
                  class="d-flex gap-3 align-items-end">
                @csrf
                <div>
                    <label class="form-label fw-semibold mb-1">Zone Code <span class="text-danger">*</span></label>
                    <input type="text" name="zone_code" class="form-control" style="width:120px;" placeholder="Z1">
                </div>
                <div>
                    <label class="form-label fw-semibold mb-1">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" name="zone_name" class="form-control" style="width:220px;">
                </div>
                <input type="hidden" name="status" value="Authorized">
                <button type="submit" class="btn btn-primary"
                        style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Add Zone</button>
            </form>
        </div>
    </div>
    @endcan
</x-app-layout>
