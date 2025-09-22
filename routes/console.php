<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:post-scheduler')->everyFiveMinutes();
Schedule::command('app:fetch-linkedin-feeds')->twiceDailyAt(12, 18, 15);
Schedule::command('calls:send-reminders')->everyFifteenMinutes();

// Running the scheduler
// * * * * * cd /home/tubetargeterapp/app.linkdominator.com && /usr/local/bin/ea-php83 artisan schedule:run >> /dev/null 2>&1