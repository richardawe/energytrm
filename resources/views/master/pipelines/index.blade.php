<x-app-layout>
    <x-slot name="title">Pipelines</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Pipeline Master</h5>
        @can('create', App\Models\Pipeline::class)
        <a href="{{ route('master.pipelines.create') }}" class="btn btn-sm btn-primary"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Pipeline</a>
        @endcan
    </div>

    @foreach($pipelines as $pipeline)
    <div class="card card-etrm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold">{{ $pipeline->code }}</span>
                <span class="text-muted ms-2">{{ $pipeline->name }}</span>
                <span class="badge bg-secondary ms-2" style="font-size:.75rem;">{{ $pipeline->commodity_type }}</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge {{ $pipeline->status === 'Authorized' ? 'badge-authorized' : 'bg-danger' }}">{{ $pipeline->status }}</span>
                <a href="{{ route('master.pipelines.show', $pipeline) }}" class="btn btn-sm btn-outline-secondary py-0">Manage</a>
            </div>
        </div>
        @if($pipeline->zones->isNotEmpty())
        <div class="card-body py-2 px-3" style="font-size:.85rem;">
            @foreach($pipeline->zones as $zone)
            <div class="mb-1">
                <span class="fw-semibold text-muted">{{ $zone->zone_code }}:</span>
                <span class="ms-1">{{ $zone->zone_name }}</span>
                @if($zone->locations->isNotEmpty())
                <span class="text-muted ms-2">—
                    {{ $zone->locations->pluck('location_code')->join(', ') }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

    @if($pipelines->isEmpty())
    <div class="text-muted text-center py-5">No pipelines defined. Add one to enable pipeline/zone/location selection on gas and power trades.</div>
    @endif
</x-app-layout>
