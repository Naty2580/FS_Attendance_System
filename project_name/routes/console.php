<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ADD THIS SCHEDULE
Schedule::command('attendance:close-sessions')->everyMinute()->withoutOverlapping();


// The Rolling Horizon job runs every night at 1:00 AM
// Schedule::command('attendance:generate-weekly')->dailyAt('01:00')->withoutOverlapping();