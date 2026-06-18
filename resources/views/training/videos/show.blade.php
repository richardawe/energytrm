<x-app-layout>
<x-slot name="title">{{ $module['title'] }}</x-slot>

<div class="container-fluid py-4">

    @php
        $quizResult  = session('quiz_result');
        $moduleIndex = collect($allModules)->search(fn($m) => $m['id'] === $module['id']);
    @endphp

    {{-- Quiz result banner --}}
    @if ($quizResult)
        @if ($quizResult['passed'])
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-3 mb-4" role="alert">
            <i class="bi bi-patch-check-fill fs-3 flex-shrink-0"></i>
            <div>
                <strong class="d-block fs-5">Module certified!</strong>
                You scored <strong>{{ $quizResult['score'] }}/{{ $quizResult['total'] }}</strong> —
                your completion has been recorded on your account.
                @if ($next)
                    Ready for <a href="{{ route('training.videos.show', $next['id']) }}" class="alert-link">{{ $next['title'] }}</a>?
                @endif
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @else
        <div class="alert alert-warning alert-dismissible d-flex align-items-center gap-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 flex-shrink-0"></i>
            <div>
                <strong class="d-block">Not quite — {{ $quizResult['score'] }}/{{ $quizResult['total'] }} correct.</strong>
                You need 9 out of 10 to pass. Review the module and try again.
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endif

    <div class="row g-4">

        {{-- Main video + quiz column --}}
        <div class="col-lg-9">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('training.videos.index') }}">Endur Training</a>
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
                    >
                        <source src="{{ asset('videos/' . $module['id'] . '-voiced.mp4') }}" type="video/mp4">
                        Your browser does not support HTML5 video.
                    </video>
                    @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5"
                         style="min-height:400px;">
                        <i class="bi bi-hourglass-split fs-1 text-secondary mb-3"></i>
                        <h5 class="text-white mb-2">Video rendering in progress</h5>
                        <p class="text-secondary small mb-0">Check back soon.</p>
                    </div>
                    @endif
                </div>

                {{-- Metadata bar --}}
                <div class="card-body d-flex flex-wrap align-items-center gap-3 border-top py-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold me-2">
                            Module {{ $moduleIndex + 1 }}
                        </span>
                        <strong>{{ $module['title'] }}</strong>
                    </div>
                    <div class="ms-auto d-flex gap-3 text-muted small align-items-center">
                        <span><i class="bi bi-collection-play me-1"></i>{{ $module['slides'] }} slides</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $module['duration'] }}</span>
                        @if ($module['available'])
                        <a href="{{ asset('videos/' . $module['id'] . '-voiced.mp4') }}"
                           download class="btn btn-outline-secondary btn-sm py-0 px-2">
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

            {{-- Quiz section --}}
            @if ($questions->isNotEmpty())
            <div id="quiz-section" class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <div>
                        <i class="bi bi-patch-question text-primary me-2 fs-5"></i>
                        <strong>Module Quiz</strong>
                        <span class="text-muted small ms-2">— 10 questions &middot; pass mark 9/10</span>
                    </div>
                    @if ($bestAttempt)
                        @if ($bestAttempt->passed)
                        <span class="badge bg-success">
                            <i class="bi bi-check-lg me-1"></i>Certified &mdash; {{ $bestAttempt->score }}/10
                        </span>
                        @else
                        <span class="badge bg-warning text-dark">
                            Best: {{ $bestAttempt->score }}/10
                        </span>
                        @endif
                    @endif
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('training.videos.quiz.submit', $module['id']) }}" id="quiz-form">
                        @csrf

                        @foreach ($questions as $qi => $q)
                        @php
                            $resultForQ = $quizResult['results'][$q->id] ?? null;
                        @endphp
                        <div class="mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <p class="fw-semibold mb-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">{{ $qi + 1 }}</span>
                                {{ $q->question }}
                            </p>

                            @foreach (['a','b','c','d'] as $letter)
                            @php
                                $optionText = $q->optionText($letter);
                                $isChosen   = $resultForQ && $resultForQ['chosen'] === $letter;
                                $isCorrect  = $resultForQ && $resultForQ['correct_option'] === $letter;
                                $bgClass    = '';
                                if ($resultForQ) {
                                    if ($isCorrect)      $bgClass = 'bg-success bg-opacity-10 border-success';
                                    elseif ($isChosen)   $bgClass = 'bg-danger bg-opacity-10 border-danger';
                                }
                            @endphp
                            <div class="form-check mb-2 p-3 rounded border {{ $bgClass }}"
                                 style="cursor:pointer;"
                                 onclick="document.getElementById('opt-{{ $q->id }}-{{ $letter }}').click()">
                                <input class="form-check-input" type="radio"
                                       name="answers[{{ $q->id }}]"
                                       id="opt-{{ $q->id }}-{{ $letter }}"
                                       value="{{ $letter }}"
                                       {{ $isChosen ? 'checked' : '' }}
                                       {{ $resultForQ ? 'disabled' : '' }}>
                                <label class="form-check-label w-100" for="opt-{{ $q->id }}-{{ $letter }}" style="cursor:pointer;">
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary me-2">{{ strtoupper($letter) }}</span>
                                    {{ $optionText }}
                                    @if ($resultForQ && $isCorrect)
                                        <i class="bi bi-check-circle-fill text-success ms-2"></i>
                                    @elseif ($resultForQ && $isChosen && !$isCorrect)
                                        <i class="bi bi-x-circle-fill text-danger ms-2"></i>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach

                        @unless ($quizResult)
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-5" id="quiz-submit">
                                <i class="bi bi-send me-2"></i>Submit Quiz
                            </button>
                        </div>
                        @else
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="text-muted small">
                                @if ($quizResult['passed'])
                                    <i class="bi bi-patch-check-fill text-success me-1"></i>Passed
                                @else
                                    Retake the quiz after reviewing the module.
                                @endif
                            </span>
                            @unless ($quizResult['passed'])
                            <a href="{{ route('training.videos.show', $module['id']) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i>Try Again
                            </a>
                            @endunless
                        </div>
                        @endunless
                    </form>
                </div>
            </div>
            @endif

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
                    @php
                        $isCurrent = $m['id'] === $module['id'];
                        $isPassed  = \App\Models\EndurQuizAttempt::where('user_id', auth()->id())
                                        ->where('module_id', $m['id'])
                                        ->where('passed', true)->exists();
                    @endphp
                    <a href="{{ route('training.videos.show', $m['id']) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3
                              {{ $isCurrent ? 'active' : '' }}">

                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                             style="width:32px;height:32px;font-size:.85rem;
                                    {{ $isCurrent
                                        ? 'background:rgba(255,255,255,.2);color:#fff;'
                                        : ($isPassed
                                            ? 'background:rgba(25,135,84,.15);color:#198754;'
                                            : 'background:rgba(13,110,253,.1);color:#0d6efd;') }}">
                            {{ $isPassed && !$isCurrent ? '✓' : ($i + 1) }}
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
                        @elseif ($isCurrent)
                            <i class="bi bi-play-fill flex-shrink-0"></i>
                        @elseif ($isPassed)
                            <i class="bi bi-patch-check-fill text-success flex-shrink-0"></i>
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

<x-slot name="scripts">
<script>
document.addEventListener('DOMContentLoaded', () => {
    const video    = document.getElementById('training-video');
    const quizSect = document.getElementById('quiz-section');

    if (video && quizSect) {
        video.addEventListener('ended', () => {
            quizSect.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    const form = document.getElementById('quiz-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            const total    = {{ $questions->count() }};
            const answered = form.querySelectorAll('input[type=radio]:checked').length;
            if (answered < total) {
                e.preventDefault();
                alert(`Please answer all ${total} questions before submitting (${answered} answered so far).`);
            }
        });
    }
});
</script>
</x-slot>

</x-app-layout>
