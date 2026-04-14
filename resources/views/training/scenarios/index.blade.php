<x-app-layout>
    <x-slot name="title">Guided Scenarios</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-semibold mb-0">Guided Scenarios</h5>
            <div class="text-muted small">Step-by-step walkthroughs of key ETRM workflows</div>
        </div>
    </div>

    @forelse($scenarios as $module => $group)
    <h6 class="text-uppercase text-muted fw-semibold mb-2" style="font-size:.75rem;letter-spacing:.08em;">
        {{ \App\Models\GuidedScenario::moduleLabel($module) }}
    </h6>

    <div class="row g-3 mb-4">
        @foreach($group as $scenario)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('training.scenarios.show', $scenario) }}" class="text-decoration-none">
                <div class="card card-etrm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-semibold" style="color:var(--etrm-dark);">{{ $scenario->title }}</span>
                            <span class="badge bg-secondary ms-2" style="white-space:nowrap;">
                                {{ $scenario->stepCount() }} steps
                            </span>
                        </div>
                        <p class="text-muted small mb-0">{{ $scenario->description }}</p>
                    </div>
                    <div class="card-footer py-2 text-end">
                        <span class="small" style="color:var(--etrm-secondary);">Start walkthrough →</span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @empty
    <div class="alert alert-info">No scenarios available yet.</div>
    @endforelse
</x-app-layout>
