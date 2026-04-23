<x-app-layout>
    <x-slot name="title">{{ $contractType->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <a href="{{ route('master.contract-types.index') }}" class="text-muted small text-decoration-none">Contract Types</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">{{ $contractType->name }}</span>
        </div>
        <a href="{{ route('master.contract-types.edit', $contractType) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fw-semibold">{{ $contractType->name }}</span>
            @include('partials._status_badge', ['status' => $contractType->is_active ? 'Active' : 'Inactive'])
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Name</dt>
                <dd class="col-sm-8">{{ $contractType->name }}</dd>

                <dt class="col-sm-4 text-muted">Code</dt>
                <dd class="col-sm-8"><code>{{ $contractType->code }}</code></dd>

                <dt class="col-sm-4 text-muted">Incoterm</dt>
                <dd class="col-sm-8">{{ $contractType->incoterm ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Description</dt>
                <dd class="col-sm-8">{{ $contractType->description ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Created</dt>
                <dd class="col-sm-8">{{ $contractType->created_at->format('d M Y H:i') }}</dd>

                <dt class="col-sm-4 text-muted">Updated</dt>
                <dd class="col-sm-8">{{ $contractType->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</x-app-layout>
