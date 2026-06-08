<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily EoB/CoB checklist reset at midnight (cPanel cron: php artisan schedule:run every minute)
Schedule::command('etrm:reset-checklists')->dailyAt('00:00');

// Live market data: fetch commodity prices at 07:00 and 18:00 UTC
// (covers EIA daily publish ~17:30 EST and FRED morning updates)
Schedule::command('etrm:fetch-prices')->twiceDaily(7, 18);

// Live FX rates: refresh every hour during business hours
Schedule::command('etrm:fetch-fx')->hourlyAt(5)->between('06:00', '22:00');

// Future: mark invoices overdue at 08:00 every morning
// Schedule::command('etrm:mark-overdue-invoices')->dailyAt('08:00');
