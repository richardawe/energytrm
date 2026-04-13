<x-app-layout>
    <x-slot name="title">{{ $module }}</x-slot>
    <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
        <div class="text-center text-muted">
            <div style="font-size: 3rem;">🚧</div>
            <h4 class="mt-3">{{ $module }}</h4>
            <p class="small">This module is coming in a future phase.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
        </div>
    </div>
</x-app-layout>
