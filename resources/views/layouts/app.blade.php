<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — EnergyTRM' : 'EnergyTRM' }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>

{{-- Top navbar --}}
<nav class="navbar navbar-expand-lg navbar-etrm">
    <div class="container-fluid px-3">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <span style="color: var(--etrm-accent);">&#9889;</span> EnergyTRM
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.*') ? 'active' : '' }}"
                       href="{{ route('master.dashboard') }}">Master Data</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('trades.*') ? 'active' : '' }}"
                       href="{{ route('trades.index') }}">Physical Trades</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('operations.*') ? 'active' : '' }}"
                       href="{{ route('operations.dashboard') }}">Operations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('financials.*') ? 'active' : '' }}"
                       href="{{ route('financials.dashboard') }}">Financials</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('risk.*') ? 'active' : '' }}"
                       href="{{ route('risk.dashboard') }}">Risk &amp; Analytics</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('training.*') ? 'active' : '' }}"
                       href="{{ route('training.scenarios.index') }}">Training</a>
                </li>
                @if(Auth::user()->isAdmin())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">User Management</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.audit.index') }}">Audit Log</a></li>
                    </ul>
                </li>
                @endif
            </ul>

            {{-- User dropdown --}}
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <span style="color: #fff;">{{ Auth::user()->name }}</span>
                        <span class="badge ms-1"
                              style="background: var(--etrm-accent); color:#000; font-size:0.65rem;">
                            {{ ucfirst(Auth::user()->role ?? 'user') }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Sign Out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Flash messages --}}
<div class="container-fluid px-3 pt-2">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show py-2 mb-2" role="alert">
            {!! session('warning') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

{{-- Page content --}}
<div class="etrm-content">
    {{ $slot }}
</div>

</body>
</html>
