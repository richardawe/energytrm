@extends('layouts.app')

@section('title', $module['title'] . ' — Endur Training')

@section('content')
<div class="container-fluid py-4">

    <div class="row g-4">

        {{-- Main video column --}}
        <div class="col-lg-9">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('training.videos.index') }}">Training Videos</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $module['title'] }}</li>
                </ol>
            </nav>

            {{-- Video player --}}
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div style="background:#061020;">
                    @if ($module['available'])
                    <video
                        id="training-video"
                        class="w-100"
                        style="max-height:540px;display:block;"
                        controls
                        preload="metadata"
                        poster="{{ asset('slide-images/slide-' . str_pad(1, 3, '0', STR_PAD_LEFT) . '.png') }}"
                    >
                        <source src="{{ asset('videos/' . $module['id'] . '.mp4') }}" type="video/mp4">
                        Your browser does not support HTML5 video.
                    </video>
                    @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5"
                         style="min-height:400px;">
                        <i class="bi bi-hourglass-split fs-1 text-secondary mb-3"></i>
                        <h5 class="text-white mb-2">Video rendering in progress</h5>
                        <p class="text-secondary small mb-0">
                            Run <code class="text-info">cd video-generator && bash scripts/render-all.sh</code>
                            to generate MP4s.
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Video metadata bar --}}
                <div class="card-body d-flex flex-wrap align-items-center gap-3 border-top py-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold me-2">
                            Module {{ collect($allModules)->search(fn($m) => $m['id'] === $module['id']) + 1 }}
                        </span>
                        <strong>{{ $module['title'] }}</strong>
                    </div>
                    <div class="ms-auto d-flex gap-3 text-muted small">
                        <span><i class="bi bi-collection-play me-1"></i>{{ $module['slides'] }} slides</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $module['duration'] }}</span>
                        @if ($module['available'])
                        <a href="{{ asset('videos/' . $module['id'] . '.mp4') }}"
                           download
                           class="btn btn-outline-secondary btn-sm py-0 px-2">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">About this module</h6>
                <p class="text-muted">{{ $module['description'] }}</p>
            </div>

            {{-- Prev / Next navigation --}}
            <div class="d-flex justify-content-between gap-3">
                @if ($prev)
                <a href="{{ route('training.videos.show', $prev['id']) }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">{{ $prev['title'] }}</span>
                    <span class="d-inline d-sm-none">Previous</span>
                </a>
                @else
                <div></div>
                @endif

                @if ($next)
                <a href="{{ route('training.videos.show', $next['id']) }}"
                   class="btn btn-primary d-flex align-items-center gap-2">
                    <span class="d-none d-sm-inline">{{ $next['title'] }}</span>
                    <span class="d-inline d-sm-none">Next</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
        </div>

        {{-- Sidebar: module list --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom fw-bold py-3">
                    <i class="bi bi-collection-play me-2 text-primary"></i>All Modules
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($allModules as $i => $m)
                    <a href="{{ route('training.videos.show', $m['id']) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3
                              {{ $m['id'] === $module['id'] ? 'active' : '' }}">

                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                             style="width:32px;height:32px;font-size:.85rem;
                                    {{ $m['id'] === $module['id']
                                        ? 'background:rgba(255,255,255,.2);color:#fff;'
                                        : 'background:rgba(13,110,253,.1);color:#0d6efd;' }}">
                            {{ $i + 1 }}
                        </div>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate small">{{ $m['title'] }}</div>
                            <div class="d-flex gap-2 mt-1" style="font-size:.75rem;opacity:.7;">
                                <span>{{ $m['slides'] }} slides</span>
                                <span>&middot;</span>
                                <span>{{ $m['duration'] }}</span>
                            </div>
                        </div>

                        @if (!$m['available'])
                        <i class="bi bi-hourglass-split text-muted flex-shrink-0"></i>
                        @elseif ($m['id'] === $module['id'])
                        <i class="bi bi-play-fill flex-shrink-0"></i>
                        @else
                        <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body text-center py-4">
                    <i class="bi bi-signpost-2 fs-2 text-primary mb-2"></i>
                    <div class="fw-semibold mb-1">Practice what you've learned</div>
                    <p class="text-muted small mb-3">
                        Step through guided scenarios in the live training portal.
                    </p>
                    <a href="{{ route('training.scenarios.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        Guided Scenarios
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-play next module when video ends
document.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('training-video');
    @if ($next && $next['available'])
    if (video) {
        video.addEventListener('ended', () => {
            if (confirm('Module complete! Watch "{{ $next['title'] }}" next?')) {
                window.location.href = '{{ route('training.videos.show', $next['id']) }}';
            }
        });
    }
    @endif
});
</script>
@endpush
