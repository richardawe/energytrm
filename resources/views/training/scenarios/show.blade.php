<x-app-layout>
    <x-slot name="title">{{ $scenario->title }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('training.scenarios.index') }}" class="text-muted small text-decoration-none">← Guided Scenarios</a>
    </div>

    <div class="row g-3">
        {{-- Scenario header --}}
        <div class="col-12">
            <div class="card card-etrm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-semibold mb-1">{{ $scenario->title }}</h5>
                            <p class="text-muted mb-0">{{ $scenario->description }}</p>
                        </div>
                        <span class="badge bg-secondary ms-3" style="white-space:nowrap;">
                            {{ $scenario->stepCount() }} steps
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Steps --}}
        <div class="col-lg-8">
            @foreach($scenario->steps as $i => $step)
            <div class="card card-etrm mb-3" id="step-{{ $i + 1 }}">
                <div class="card-header d-flex align-items-center gap-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white"
                          style="width:28px;height:28px;font-size:.8rem;background:var(--etrm-secondary);flex-shrink:0;">
                        {{ $i + 1 }}
                    </span>
                    <span class="fw-semibold">{{ $step['title'] }}</span>
                </div>
                <div class="card-body">
                    <p class="mb-3" style="line-height:1.6;">{{ $step['instruction'] }}</p>

                    @if(!empty($step['fields']))
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Key fields in this step:</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($step['fields'] as $fieldName)
                            <span class="badge" style="background:var(--etrm-secondary);font-size:.75rem;">
                                {{ str_replace('_', ' ', ucfirst($fieldName)) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($step['route_name']))
                        @php
                            try { $url = route($step['route_name']); } catch (\Exception $e) { $url = null; }
                        @endphp
                        @if($url)
                        <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                            Open {{ ucfirst(str_replace(['.', '_', '-'], ' ', last(explode('.', $step['route_name'])))) }} ↗
                        </a>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Navigation --}}
            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('training.scenarios.index') }}" class="btn btn-outline-secondary btn-sm">← All Scenarios</a>
            </div>
        </div>

        {{-- Step navigator sidebar --}}
        <div class="col-lg-4">
            <div class="card card-etrm" style="position:sticky;top:1rem;">
                <div class="card-header fw-semibold">Steps</div>
                <div class="list-group list-group-flush">
                    @foreach($scenario->steps as $i => $step)
                    <a href="#step-{{ $i + 1 }}"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2"
                       style="font-size:.85rem;">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white"
                              style="width:20px;height:20px;font-size:.7rem;background:var(--etrm-secondary);flex-shrink:0;">
                            {{ $i + 1 }}
                        </span>
                        {{ $step['title'] }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
