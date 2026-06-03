<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send 5-day advance cleaning reminders every morning at 8:00 AM
Schedule::command('reminders:send-cleaning')->dailyAt('08:00')->evenInMaintenanceMode();
