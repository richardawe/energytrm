<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — EnergyTRM' : 'EnergyTRM' }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body style="background: #1a3c5e; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<div class="card shadow-lg" style="width: 100%; max-width: 420px; border-radius: 0.5rem; overflow: hidden;">
    <div class="card-header text-center py-4" style="background: #1a3c5e; border-bottom: 3px solid #e8a020;">
        <div style="font-size: 2rem; color: #e8a020;">&#9889;</div>
        <div style="color: #fff; font-size: 1.4rem; font-weight: 700; letter-spacing: 0.05em;">EnergyTRM</div>
        <div style="color: #c8d3e0; font-size: 0.75rem; margin-top: 0.25rem;">Energy Trading &amp; Risk Management</div>
    </div>
    <div class="card-body p-4">
        {{ $slot }}
    </div>
</div>

</body>
</html>
