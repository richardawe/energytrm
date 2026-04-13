<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily EoB/CoB checklist reset at midnight (cPanel cron: php artisan schedule:run every minute)
Schedule::command('etrm:reset-checklists')->dailyAt('00:00');

// Future: mark invoices overdue at 08:00 every morning
// Schedule::command('etrm:mark-overdue-invoices')->dailyAt('08:00');
