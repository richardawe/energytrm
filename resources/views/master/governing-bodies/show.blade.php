<x-app-layout>
    <x-slot name="title">{{ $governingBody->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <a href="{{ route('master.governing-bodies.index') }}" class="text-muted small text-decoration-none">Governing Bodies</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">{{ $governingBody->name }}</span>
        </div>
        <a href="{{ route('master.governing-bodies.edit', $governingBody) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fw-semibold">{{ $governingBody->name }}</span>
            @include('partials._status_badge', ['status' => $governingBody->is_active ? 'Active' : 'Inactive'])
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Name</dt>
                <dd class="col-sm-8">{{ $governingBody->name }}</dd>

                <dt class="col-sm-4 text-muted">Jurisdiction</dt>
                <dd class="col-sm-8">{{ $governingBody->jurisdiction ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Country</dt>
                <dd class="col-sm-8">{{ $governingBody->country ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">Version</dt>
                <dd class="col-sm-8">{{ $governingBody->version }}</dd>

                <dt class="col-sm-4 text-muted">Created</dt>
                <dd class="col-sm-8">{{ $governingBody->created_at->format('d M Y H:i') }}</dd>

                <dt class="col-sm-4 text-muted">Updated</dt>
                <dd class="col-sm-8">{{ $governingBody->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</x-app-layout>
