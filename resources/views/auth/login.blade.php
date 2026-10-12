<x-guest-layout>
    <x-slot name="title">Sign In</x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-muted small">Forgot password?</a>
            @endif
            <button type="submit" class="btn btn-primary px-4"
                    style="background: var(--etrm-secondary); border-color: var(--etrm-secondary);">
                Sign In
            </button>
        </div>
    </form>

    <hr class="my-4">

    <div class="text-center">
        <div class="text-muted small mb-2">New here? Explore the full portal before signing up.</div>
        <form method="POST" action="{{ route('trial.start') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">
                Try it free for 1 hour
            </button>
        </form>
        <div class="small mt-2">
            or <a href="{{ route('register') }}">create an account</a>
        </div>
    </div>
</x-guest-layout>
