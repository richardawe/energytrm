@extends('layouts.app')

@section('title', 'Endur Training Videos')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Endur Training Videos</h1>
            <p class="text-muted mb-0">
                {{ $totalSlides }} slides across 6 modules &mdash; {{ $totalDuration }} total runtime
            </p>
        </div>
        <a href="{{ route('training.scenarios.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-signpost-2 me-1"></i>Guided Scenarios
        </a>
    </div>

    {{-- Module grid --}}
    <div class="row g-4">
        @foreach ($modules as $i => $mod)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 {{ $mod['available'] ? '' : 'opacity-75' }}"
                 style="transition: transform .15s ease, box-shadow .15s ease;"
                 onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.15)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">

                {{-- Colour strip --}}
                <div style="height:4px;background:linear-gradient(90deg,#0d6efd,#0dcaf0);border-radius:4px 4px 0 0;"></div>

                <div class="card-body d-flex flex-column p-4">
                    {{-- Module badge + icon --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:48px;height:48px;background:rgba(13,110,253,.1);">
                            <i class="bi {{ $mod['icon'] }} fs-4 text-primary"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                Module {{ $i + 1 }}
                            </span>
                            @unless($mod['available'])
                            <span class="badge bg-secondary ms-1">Rendering soon</span>
                            @endunless
                        </div>
                    </div>

                    <h5 class="card-title fw-bold mb-2">{{ $mod['title'] }}</h5>
                    <p class="card-text text-muted small flex-grow-1">{{ $mod['description'] }}</p>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex gap-3 text-muted small">
                            <span><i class="bi bi-collection-play me-1"></i>{{ $mod['slides'] }} slides</span>
                            <span><i class="bi bi-clock me-1"></i>{{ $mod['duration'] }}</span>
                        </div>
                        @if ($mod['available'])
                        <a href="{{ route('training.videos.show', $mod['id']) }}"
                           class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-play-fill me-1"></i>Watch
                        </a>
                        @else
                        <button class="btn btn-secondary btn-sm px-3" disabled>
                            <i class="bi bi-hourglass-split me-1"></i>Soon
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Info footer --}}
    <div class="mt-5 p-4 rounded-3 border bg-light">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-info-circle text-primary me-2"></i>About these videos
                </h6>
                <p class="text-muted small mb-0">
                    Videos are generated from the official Endur training slides.
                    Each module covers a specific area of the Endur ETRM platform.
                    Watch them in order for a complete introduction, or jump to the module most
                    relevant to your role.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('training.scenarios.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-signpost-2 me-1"></i>Try a guided scenario
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
